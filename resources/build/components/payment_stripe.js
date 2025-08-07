import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";
import { _confirmation as c_confirmation } from "./overlays.js";

import { loadStripe } from '@stripe/stripe-js';

let _payment_stripe = {
    name: "c-payment_stripe",
    components: {
        "c-confirmation": c_confirmation,
    },
    emits: [
        "validate",
        "load",
        "unload",
        "success",
        "fail",
    ],
    props: {
        mode: {
            type: String,
            default: 'payment'
        },
        can_save_cards: {
            type: Boolean,
            default: false
        },
        is_valid: {
            type: Boolean,
            default: true
        },
        customer_id: {
            default: null
        },
        billing_address_city: {
            default: null
        },
        billing_address_country: {
            default: null
        },
        billing_address_line1: {
            default: null
        },
        billing_address_line2: {
            default: null
        },
        billing_address_postal_code: {
            default: null
        },
        billing_address_state: {
            default: null
        },
        billing_email: {
            default: null
        },
        billing_name: {
            default: null
        },
        billing_phone: {
            default: null
        },
        amount: {
            required: true
        },
        additional_data: {
            default: null
        },
        redirect_url: {
            default: null
        }
    },
    data() {
        return {
            can_off_session: false,
            customer_cards: [],
            is_disabled: false,
            stripe: null,
            stripe_elements: null,
            stripe_payment: null,
            stripe_payment_element: null,
            is_payment_valid: false,
            is_payment_element_ready: false,
            empty_amount: "0.00",
            save_card: false,
            off_session: false,
            selected_customer_card: null
        };
    },
    watch: {
        mode: {
            handler(new_val, old_val) {
                if (this.stripe_elements && new_val != old_val) {
                    this.stripe_elements.update({
                        mode: this.generated_mode,
                        amount: this.generated_amount,
                        setupFutureUsage: this.generated_setup_future_usage,
                    });
                }
            }
        },
        amount_pence: {
            handler(new_val, old_val) {
                if (this.stripe_elements && new_val != old_val) {
                    this.stripe_elements.update({
                        mode: this.generated_mode,
                        amount: this.generated_amount,
                        setupFutureUsage: this.generated_setup_future_usage,
                    });
                }
            }
        },
        save_card: {
            handler(new_val, old_val) {
                if (this.stripe_elements && new_val != old_val) {
                    this.stripe_elements.update({
                        mode: this.generated_mode,
                        amount: this.generated_amount,
                        setupFutureUsage: this.generated_setup_future_usage,
                    });
                }
            }
        },
        billing_address_country: {
            handler(new_val, old_val) {
                if (this.stripe_payment && new_val != old_val && new_val != null) {
                    const update_payload = {
                        fields: {
                            billingDetails: {
                                address: {
                                    country: new_val !== "" ? "never" : "auto"
                                }
                            }
                        }
                    };
                    this.stripe_payment.update(update_payload);
                }
            },
            immediate: true
        },
        billing_address_postal_code: {
            handler(new_val, old_val) {
                if (this.stripe_payment && new_val != old_val && new_val != null) {
                    const update_payload = {
                        fields: {
                            billingDetails: {
                                address: {
                                    postalCode: new_val !== "" ? "never" : "auto"
                                }
                            }
                        }
                    };
                    this.stripe_payment.update(update_payload);
                }
            },
            immediate: true
        },
        customer_id: {
            handler(new_val, old_val) {
                if (this.stripe_payment && new_val != old_val && new_val != null) {
                    this.getCustomerCards();
                }
            }
        },
    },
    methods: {
        transformErrorResponse(response) {
            return {
                success: false,
                success_message: null,
                error: true,
                error_message: response.error.message,
                http_code: 401,
                http_message: response.error.code !== undefined ? response.error.code : "Error",
                data: response.error
            };
        },
        getCustomerCards() {
            if (this.can_save_cards && this.customer_id) {
                let data = {
                    customer_id: this.customer_id
                };

                _api.request({
                    url: _config.get("api.payment_stripe.get_customers_cards"),
                    method: "post",
                    data: data
                })
                .then((response) => {
                    response.data.data.customer_cards.forEach((customer_card) => {
                        customer_card.is_loading = false;
                        customer_card.confirm_deletion = false;
                    });
                    this.customer_cards = response.data.data.customer_cards;
                    this.$emit("unload");
                })
            }
        },
        selectCustomerCard(customer_card) {
            if (
                customer_card == null
                || customer_card !== null && this.selected_customer_card != null && this.selected_customer_card.id == customer_card.id
                || this.generated_mode == "setup"
            ) {
                this.selected_customer_card = null;
                this.stripe_payment.update({
                    readOnly: false
                });
            } else {
                this.selected_customer_card = customer_card;
                this.stripe_payment.update({
                    readOnly: true
                });
                this.save_card = false;
            }
        },
        deleteCustomerCard(selected_customer_card) {
            selected_customer_card.confirm_deletion = false;
            selected_customer_card.is_loading = true;

            let data = {
                id: selected_customer_card.id
            };

            _api.request({
                url: _config.get("api.payment_stripe.delete_customers_cards"),
                method: "delete",
                data: data
            })
            .then((response) => {
                selected_customer_card.is_loading = false;
                if (response.data.success == true) {
                    this.customer_cards.forEach((customer_card, customer_card_index) => {
                        if (selected_customer_card.id == customer_card.id) {
                            this.customer_cards.splice(customer_card_index, 1);
                        }
                    });
                }
            });
        },
        handleSetup(stripe_payment_method_options) {
            this.stripe
                .createPaymentMethod(stripe_payment_method_options)
                .then((result) => {
                    if (result.error) {
                        this.is_disabled = false;
                        this.$emit("fail", this.transformErrorResponse(result));
                    } else {
                        let data = {
                            payment_method_id: result.paymentMethod.id
                        };

                        if (this.customer_id) {
                            data.customer_id = this.customer_id;
                        }

                        if (this.additional_data) {
                            Object.entries(this.additional_data).forEach(additional_data_item => {
                                const [prop, value] = additional_data_item;
                                if (data[prop] === undefined) {
                                    data[prop] = value;
                                }
                            });
                        }

                        if (this.redirect_url) {
                            data.redirect_url = this.redirect_url;
                        }

                        _api.request({
                            url: _config.get("api.payment_stripe.setup_intent"),
                            method: "post",
                            data: data
                        })
                        .then((response) => {
                            this.handleSetupResponse(response);
                        })
                        .catch((error) => {
                            this.handleSetupResponse(error.response);
                        });
                    }
                });
        },
        handleSetupResponse(response) {
            if (response.data.data != null && response.data.error == true) {
                this.is_disabled = false;
                this.selected_customer_card = null;
                this.$emit("fail", response);
            } else if (response.data.data != null && response.data.data.requires_action) {
                this.stripe
                    .confirmCardSetup(response.data.data.intent_client_secret)
                    .then((result) => {
                        if (result.error) {
                            this.is_disabled = false;
                            this.selected_customer_card = null;
                            this.$emit("fail", this.transformErrorResponse(result));
                        } else {
                            let data = {
                                amount: this.amount_pence,
                                amount_display: this.amount_display,
                                amount_pence: this.amount_pence,
                                amount_pounds: this.amount_pounds,
                                setup_intent_id: result.setupIntent.id,
                                customer_id: response.data.data.customer,
                            };

                            if (this.additional_data) {
                                Object.entries(this.additional_data).forEach(additional_data_item => {
                                    const [prop, value] = additional_data_item;
                                    if (data[prop] === undefined) {
                                        data[prop] = value;
                                    }
                                });
                            }

                            if (this.redirect_url) {
                                data.redirect_url = this.redirect_url;
                            }

                            _api.request({
                                url: _config.get("api.payment_stripe.setup_intent"),
                                method: "post",
                                data: data
                            })
                            .then((response) => {
                                this.is_disabled = false;
                                this.selected_customer_card = null;
                                this.handleSetupResponse(response);
                            })
                            .catch((error) => {
                                this.handleSetupResponse(error.response);
                            });
                        }
                    });
            } else {
                this.save_card = false;
                this.off_session = false;
                this.is_disabled = false;
                this.selected_customer_card = null;
                this.$emit("success", response);
                this.getCustomerCards();
            }
        },
        handlePayment(stripe_payment_method_options) {
            this.stripe
                .createPaymentMethod(stripe_payment_method_options)
                .then((result) => {
                    if (result.error) {
                        this.is_disabled = false;
                        this.$emit("fail", this.transformErrorResponse(result));
                    } else {
                        let data = {
                            off_session: this.off_session,
                            setup_future_usage: this.generated_setup_future_usage,
                            amount: this.amount_pence,
                            amount_display: this.amount_display,
                            amount_pence: this.amount_pence,
                            amount_pounds: this.amount_pounds,
                            payment_method_id: result.paymentMethod.id,
                        };

                        if (this.customer_id) {
                            data.customer_id = this.customer_id;
                        }

                        if (this.additional_data) {
                            Object.entries(this.additional_data).forEach(additional_data_item => {
                                const [prop, value] = additional_data_item;
                                if (data[prop] === undefined) {
                                    data[prop] = value;
                                }
                            });
                        }

                        if (this.redirect_url) {
                            data.redirect_url = this.redirect_url;
                        }

                        _api.request({
                            url: _config.get("api.payment_stripe.payment_intent"),
                            method: "post",
                            data: data
                        })
                        .then((response) => {
                            this.is_disabled = false;
                            this.handlePaymentResponse(response);
                        })
                        .catch((error) => {
                            this.handlePaymentResponse(error.response);
                        });
                    }
                });
        },
        handlePaymentResponse(response) {
            if (response.data.data != null && response.data.error == true) {
                this.is_disabled = false;
                this.selected_customer_card = null;
                this.$emit("fail", response);
            } else if (response.data.data != null && response.data.data.requires_action) {
                this.stripe
                    .handleCardAction(response.data.data.intent_client_secret)
                    .then((result) => {
                        if (result.error) {
                            this.is_disabled = false;
                            this.selected_customer_card = null;
                            this.$emit("fail", this.transformErrorResponse(result));
                        } else {
                            let data = {
                                off_session: this.off_session,
                                amount: this.amount_pence,
                                amount_display: this.amount_display,
                                amount_pence: this.amount_pence,
                                amount_pounds: this.amount_pounds,
                                payment_intent_id: result.paymentIntent.id,
                                customer_id: response.data.data.customer,
                            };

                            if (this.additional_data) {
                                Object.entries(this.additional_data).forEach(additional_data_item => {
                                    const [prop, value] = additional_data_item;
                                    if (data[prop] === undefined) {
                                        data[prop] = value;
                                    }
                                });
                            }

                            if (this.redirect_url) {
                                data.redirect_url = this.redirect_url;
                            }

                            _api.request({
                                url: _config.get("api.payment_stripe.payment_intent"),
                                method: "post",
                                data: data
                            })
                            .then((response) => {
                                this.is_disabled = false;
                                this.selected_customer_card = null;
                                this.handlePaymentResponse(response);
                            })
                            .catch((error) => {
                                this.handlePaymentResponse(error.response);
                            });
                        }
                    });
            } else {
                this.save_card = false;
                this.off_session = false;
                this.is_disabled = false;
                this.selected_customer_card = null;
                this.$emit("success", response);
                this.getCustomerCards();
            }
        },
        submit() {
            this.$emit("validate");

            if (this.is_disabled || !this.is_valid) {
                return;
            }

            this.is_disabled = true;
            this.$emit("load");

            if (this.selected_customer_card) {
                let data = {
                    off_session: this.off_session,
                    setup_future_usage: this.generated_setup_future_usage,
                    amount: this.amount_pence,
                    amount_display: this.amount_display,
                    amount_pence: this.amount_pence,
                    amount_pounds: this.amount_pounds,
                    payment_method_id: this.selected_customer_card.id,
                    customer_id: this.customer_id,
                };

                if (this.additional_data) {
                    Object.entries(this.additional_data).forEach(additional_data_item => {
                        const [prop, value] = additional_data_item;
                        if (data[prop] === undefined) {
                            data[prop] = value;
                        }
                    });
                }

                if (this.redirect_url) {
                    data.redirect_url = this.redirect_url;
                }

                _api.request({
                    url: this.generated_mode == "subscription" ? _config.get("api.payment_stripe.setup_intent") : _config.get("api.payment_stripe.payment_intent"),
                    method: "post",
                    data: data
                })
                .then((response) => {
                    this.is_disabled = false;
                    if (this.generated_mode == "subscription") {
                        this.handleSetupResponse(response);
                    } else {
                        this.handlePaymentResponse(response);
                    }
                })
                .catch((error) => {
                    if (this.generated_mode == "subscription") {
                        this.handleSetupResponse(response);
                    } else {
                        this.handlePaymentResponse(response);
                    }
                });

            } else {
                this.stripe_elements.submit().then((result) => {
                    if (result.error !== undefined) {
                        this.$emit("fail", this.transformErrorResponse(result));
                    } else {
                        let stripe_payment_method_options = {
                            elements: this.stripe_elements
                        };

                        stripe_payment_method_options.params = {
                            billing_details: {
                                address: {
                                    city: null,
                                    country: null,
                                    line1: null,
                                    line2: null,
                                    postal_code: null,
                                    state: null
                                },
                                email: null,
                                name: null,
                                phone: null
                            }
                        };

                        Object.entries(stripe_payment_method_options.params.billing_details).forEach(billing_detail => {
                            const [billing_detail_property, billing_detail_value] = billing_detail;
                            if (billing_detail_property == "address") {
                                Object.entries(billing_detail_value).forEach(billing_address_detail => {
                                    const [billing_address_detail_property, billing_address_detail_value] = billing_address_detail;
                                    if (this["billing_address_" + billing_address_detail_property]) {
                                        stripe_payment_method_options.params.billing_details.address[billing_address_detail_property] = this["billing_address_" + billing_address_detail_property];
                                    }
                                });
                            } else {
                                if (this["billing_" + billing_detail_property]) {
                                    stripe_payment_method_options.params.billing_details[billing_detail_property] = this["billing_" + billing_detail_property];
                                }
                            }
                        });

                        if (["setup", "subscription"].includes(this.generated_mode)) {
                            this.handleSetup(stripe_payment_method_options);
                        }

                        if (["payment"].includes(this.generated_mode)) {
                            this.handlePayment(stripe_payment_method_options);
                        }
                    }
                });
            }
        }
    },
    computed: {
        generated_mode() {
            return (this.mode === "subscription") ? "subscription" : (parseFloat(this.amount_pence) == 0 ? "setup" : "payment");
        },
        generated_amount() {
            const generated_amount = parseFloat(this.amount_pence);
            if (generated_amount == 0) {
                this.selectCustomerCard(null);
            }
            return generated_amount;
        },
        generated_setup_future_usage() {
            return (this.mode === "subscription") ? "off_session" : (parseFloat(this.amount_pence) == 0 ? "off_session" : (this.save_card ? "off_session" : "on_session"));
        },
        amount_display() {
            let amount_display = this.empty_amount;
            let amount = this.amount;
            amount = amount.toString().replace(/[^0-9\.]/g, "");
            if (amount != "") {
                amount_display = parseFloat(amount).toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,");
            }
            return amount_display;
        },
        amount_pence() {
            return this.amount_display == this.empty_amount ? 0 : this.amount_display.toString().replace(/[^0-9]/g, "");
        },
        amount_pounds() {
            return this.amount_display == this.empty_amount ? 0 : this.amount_display.toString().replace(/[^0-9\.]/g, "");
        },
        amount_currency() {
            return new Intl.NumberFormat("en-GB", { style: "currency", currency: "gbp" }).format(this.amount_pounds);
        }
    },
    async mounted() {
        this.stripe_payment_element = this.$el.querySelector(".stripe_payment_element");

        if (this.stripe_payment_element !== undefined && this.stripe_payment_element) {
            let stripe_public_key = _config.get("payments.stripe.public_key");

            this.stripe = await loadStripe(stripe_public_key);

            let elements_options = {
                currency: "gbp",
                mode: this.generated_mode,
                amount: this.generated_amount,
                setupFutureUsage: this.generated_setup_future_usage,
                captureMethod: "manual",
                paymentMethodCreation: "manual",
                appearance: {
                    theme: "stripe",
                    variables: {
                        colorPrimary: getComputedStyle(document.documentElement).getPropertyValue("--color_primary").trim(),
                        colorBackground: getComputedStyle(document.documentElement).getPropertyValue("--color_background").trim(),
                        colorText: getComputedStyle(document.documentElement).getPropertyValue("--color_text").trim(),
                        colorDanger: "#96002d",
                    }
                }
            };

            this.stripe_elements = this.stripe.elements(elements_options);

            let payment_elements_options = {};

            if (this.billing_address_postal_code != '') {
                if (payment_elements_options.defaultValues == undefined) {
                    payment_elements_options.defaultValues = {};
                }
                if (payment_elements_options.defaultValues.billingDetails == undefined) {
                    payment_elements_options.defaultValues.billingDetails = {};
                }
                if (payment_elements_options.defaultValues.billingDetails.address == undefined) {
                    payment_elements_options.defaultValues.billingDetails.address = {};
                }
                payment_elements_options.defaultValues.billingDetails.address.postal_code = this.billing_address_postal_code;

                if (payment_elements_options.fields == undefined) {
                    payment_elements_options.fields = {};
                }
                if (payment_elements_options.fields.billingDetails == undefined) {
                    payment_elements_options.fields.billingDetails = {};
                }
                if (payment_elements_options.fields.billingDetails.address == undefined) {
                    payment_elements_options.fields.billingDetails.address = {};
                }
                payment_elements_options.fields.billingDetails.address.postalCode = 'never';
            }

            if (this.billing_address_country != '') {
                if (payment_elements_options.defaultValues == undefined) {
                    payment_elements_options.defaultValues = {};
                }
                if (payment_elements_options.defaultValues.billingDetails == undefined) {
                    payment_elements_options.defaultValues.billingDetails = {};
                }
                if (payment_elements_options.defaultValues.billingDetails.address == undefined) {
                    payment_elements_options.defaultValues.billingDetails.address = {};
                }
                payment_elements_options.defaultValues.billingDetails.address.country = this.billing_address_country;

                if (payment_elements_options.fields == undefined) {
                    payment_elements_options.fields = {};
                }
                if (payment_elements_options.fields.billingDetails == undefined) {
                    payment_elements_options.fields.billingDetails = {};
                }
                if (payment_elements_options.fields.billingDetails.address == undefined) {
                    payment_elements_options.fields.billingDetails.address = {};
                }
                payment_elements_options.fields.billingDetails.address.country = 'never';
            }

            this.stripe_payment = this.stripe_elements.create("payment", payment_elements_options);

            this.stripe_payment.mount(this.stripe_payment_element);

            this.stripe_payment.on('ready', () => {
                this.is_payment_element_ready = true;
                this.getCustomerCards();
            });

            this.stripe_payment.on('change', (event) => {
                this.is_payment_valid = event.complete;
            });
        }
    },
    template: `
    <div class="field__wrapper--card" v-show="stripe !== null && is_payment_element_ready">
        <template v-if="customer_cards.length > 0">
            <label class="field__title" v-if="generated_mode == 'setup'">Saved Payment Methods</label>
            <label class="field__title" v-else>Select A Saved Payment Method</label>
            <div class="customer_cards_wrapper amex diners discover jcb maestro mastercard unionpay visa" v-if="customer_cards.length > 0">
                <div class="customer_card" :class="[customer_card.brand, { is_selected: selected_customer_card && selected_customer_card.id == customer_card.id }]" v-for="customer_card in customer_cards" @click.stop="selectCustomerCard(customer_card)">
                <div class="customer_card__inner">
                <c-confirmation :trigger="customer_card.confirm_deletion" @open="customer_card.confirm_deletion = true" @close="customer_card.confirm_deletion = false" confirm="Delete" cancel="Cancel" @confirm="deleteCustomerCard(customer_card)">
                    <h3>Delete Saved Card</h3><p>Are you sure you'd like to delete your saved card?</p>
                </c-confirmation>
                <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: customer_card.is_loading == true }">
                    <div class="spinner"></div>
                </div>
                <div class="customer_card__dismiss" @click.stop="customer_card.confirm_deletion = true"></div><span class="customer_card__expiry"><small>Expiry Date:</small> {{ customer_card.expiry }}</span><span class="customer_card__number">{{ customer_card.number }}</span></div></div>
            </div>
        </template>
        <div class="field__wrapper">
            <label class="field__title">Enter {{ (customer_cards.length > 0) ? "New " : "" }}Payment Method Details</label>
            <div class="stripe_payment_element" :disabled="selected_customer_card != null"></div>
        </div>
        <a class="btn bg-primary border-primary text-primary_contrasting block" :class="{ is_disabled: is_disabled || !is_valid || (!is_payment_valid && selected_customer_card == null) }" @click.stop="submit">
            <template v-if="generated_mode == 'setup'">Add Card</template>
            <template v-else-if="generated_mode == 'payment'">Pay {{ amount_currency }}</template>
            <template v-else-if="generated_mode == 'subscription'">Subscribe for {{ amount_currency }}</template>
            <template v-else>Pay {{ amount_currency }}</template>
        </a>
        <div class="field__collection mt-4" v-if="can_save_cards && generated_mode == 'payment'">
            <div class="field__wrapper">
                <label class="checkbox"><input aria-label="Save card for later?" name="save_card" v-model="save_card" type="checkbox" :disabled="selected_customer_card != null"><span class="text" :disabled="selected_customer_card != null">Save card for later?</span></label>
            </div>
            <div class="field__wrapper" v-if="can_off_session">
                <label class="checkbox"><input aria-label="Charge Off Session?" name="off_session" v-model="off_session" type="checkbox"><span class="text">Charge Off Session?</span></label>
            </div>
        </div>
    </div>
    `
};

export default _payment_stripe;