import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";
import { _confirmation as c_confirmation } from "./overlays.js";

import { loadStripe } from '@stripe/stripe-js';

let _payment_stripe_cards = {
    name: "c-payment_stripe_cards",
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
        can_save_cards: {
            default: false,
        },
        is_valid: {
            default: true,
        },
        customer_id: {
            default: null,
        },
        billing_address_city: {
            default: null,
        },
        billing_address_country: {
            default: null,
        },
        billing_address_line1: {
            default: null,
        },
        billing_address_line2: {
            default: null,
        },
        billing_address_postal_code: {
            default: null,
        },
        billing_address_state: {
            default: null,
        },
        billing_email: {
            default: null,
        },
        billing_name: {
            default: null,
        },
        billing_phone: {
            default: null,
        },
        redirect_url: {
            default: null,
        },
    },
    data() {
        return {
            customer_cards: [],
            is_disabled: false,
            stripe: null,
            stripe_elements: null,
            stripe_payment: null,
            stripe_payment_element: null,
            is_payment_valid: false,
            is_payment_element_ready: false,
        };
    },
    watch: {
        customer_id: {
            handler(new_val, old_val) {
                if (this.stripe_card && new_val != old_val && new_val != null) {
                    this.getCustomerCards();
                }
            },
        },
        billing_address_postal_code: {
            handler(new_val, old_val) {
                if (this.stripe_card && new_val != old_val && new_val != null) {
                    let stripe_elements_options = {
                        hidePostalCode: new_val != "",
                    };
                    stripe_elements_options.value = {
                        postalCode: new_val != "" ? new_val : "",
                    };
                    this.stripe_card.update(stripe_elements_options);
                }
            },
            immediate: true,
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
                http_message: response.error.code,
                data: response.error,
            };
        },
        getCustomerCards() {
            if (this.can_save_cards && this.customer_id) {
                let data = {
                    customer_id: this.customer_id,
                };

                _api.request({
                    url: _config.get("api.payment_stripe.get_customers_cards"),
                    method: "post",
                    data: data,
                }).then((response) => {
                    response.data.data.customer_cards.forEach(
                        (customer_card) => {
                            customer_card.is_loading = false;
                            customer_card.confirm_deletion = false;
                        }
                    );
                    this.customer_cards = response.data.data.customer_cards;
                    this.$emit("unload");
                });
            }
        },
        deleteCustomerCard(selected_customer_card) {
            selected_customer_card.confirm_deletion = false;
            selected_customer_card.is_loading = true;

            let data = {
                id: selected_customer_card.id,
            };

            _api.request({
                url: _config.get("api.payment_stripe.delete_customers_cards"),
                method: "delete",
                data: data,
            }).then((response) => {
                selected_customer_card.is_loading = false;
                if (response.data.success == true) {
                    this.customer_cards.forEach(
                        (customer_card, customer_card_index) => {
                            if (selected_customer_card.id == customer_card.id) {
                                this.customer_cards.splice(
                                    customer_card_index,
                                    1
                                );
                            }
                        }
                    );
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
                            payment_method_id: result.paymentMethod.id,
                        };

                        if (this.customer_id) {
                            data.customer_id = this.customer_id;
                        }

                        if (this.additional_data) {
                            Object.entries(this.additional_data).forEach(
                                (additional_data_item) => {
                                    const [prop, value] = additional_data_item;
                                    if (data[prop] === undefined) {
                                        data[prop] = value;
                                    }
                                }
                            );
                        }

                        if (this.redirect_url) {
                            data.redirect_url = this.redirect_url;
                        }

                        _api.request({
                            url: _config.get("api.payment_stripe.setup_intent"),
                            method: "post",
                            data: data,
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
                this.$emit("fail", response);
            } else if (
                response.data.data != null &&
                response.data.data.requires_action
            ) {
                this.stripe
                    .confirmCardSetup(response.data.data.intent_client_secret)
                    .then((result) => {
                        if (result.error) {
                            this.is_disabled = false;
                            this.$emit(
                                "fail",
                                this.transformErrorResponse(result)
                            );
                        } else {
                            let data = {
                                payment_method_id:
                                    response.data.data.payment_method,
                                customer_id: response.data.data.customer,
                            };

                            this.is_disabled = false;
                            this.selected_customer_card = null;
                            this.$emit("success", response);
                            this.getCustomerCards();
                        }
                    });
            } else {
                let data = {
                    payment_method_id: response.data.data.payment_method,
                    customer_id: response.data.data.customer,
                };

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

            this.stripe_elements.submit().then((result) => {
                if (result.error !== undefined) {
                    this.$emit("fail", this.transformErrorResponse(result));
                } else {
                    let stripe_payment_method_options = {
                        elements: this.stripe_elements,
                    };

                    stripe_payment_method_options.params = {
                        billing_details: {
                            address: {
                                city: null,
                                country: null,
                                line1: null,
                                line2: null,
                                postal_code: null,
                                state: null,
                            },
                            email: null,
                            name: null,
                            phone: null,
                        },
                    };

                    Object.entries(
                        stripe_payment_method_options.params.billing_details
                    ).forEach((billing_detail) => {
                        const [billing_detail_property, billing_detail_value] =
                            billing_detail;
                        if (billing_detail_property == "address") {
                            Object.entries(billing_detail_value).forEach(
                                (billing_address_detail) => {
                                    const [
                                        billing_address_detail_property,
                                        billing_address_detail_value,
                                    ] = billing_address_detail;
                                    if (
                                        this[
                                            "billing_address_" +
                                                billing_address_detail_property
                                        ]
                                    ) {
                                        stripe_payment_method_options.params.billing_details.address[
                                            billing_address_detail_property
                                        ] =
                                            this[
                                                "billing_address_" +
                                                    billing_address_detail_property
                                            ];
                                    }
                                }
                            );
                        } else {
                            if (this["billing_" + billing_detail_property]) {
                                stripe_payment_method_options.params.billing_details[
                                    billing_detail_property
                                ] = this["billing_" + billing_detail_property];
                            }
                        }
                    });

                    this.handleSetup(stripe_payment_method_options);
                }
            });
        },
    },
    async mounted() {
        this.stripe_payment_element = this.$el.querySelector(
            ".stripe_payment_element"
        );

        if (
            this.stripe_payment_element !== undefined &&
            this.stripe_payment_element
        ) {
            let stripe_public_key = _config.get("payments.stripe.public_key");

            this.stripe = await loadStripe(stripe_public_key);

            let elements_options = {
                currency: "gbp",
                mode: "setup",
                setupFutureUsage: "off_session",
                captureMethod: "manual",
                paymentMethodCreation: "manual",
                appearance: {
                    theme: "stripe",
                    variables: {
                        colorPrimary: getComputedStyle(document.documentElement)
                            .getPropertyValue("--color_primary")
                            .trim(),
                        colorBackground: getComputedStyle(
                            document.documentElement
                        )
                            .getPropertyValue("--color_background")
                            .trim(),
                        colorText: getComputedStyle(document.documentElement)
                            .getPropertyValue("--color_text")
                            .trim(),
                        colorDanger: "#96002d",
                    },
                },
            };

            this.stripe_elements = this.stripe.elements(elements_options);

            let payment_elements_options = {};

            if (this.billing_address_postal_code != "") {
                if (payment_elements_options.defaultValues == undefined) {
                    payment_elements_options.defaultValues = {};
                }
                if (
                    payment_elements_options.defaultValues.billingDetails ==
                    undefined
                ) {
                    payment_elements_options.defaultValues.billingDetails = {};
                }
                if (
                    payment_elements_options.defaultValues.billingDetails
                        .address == undefined
                ) {
                    payment_elements_options.defaultValues.billingDetails.address =
                        {};
                }
                payment_elements_options.defaultValues.billingDetails.address.postal_code =
                    this.billing_address_postal_code;

                if (payment_elements_options.fields == undefined) {
                    payment_elements_options.fields = {};
                }
                if (
                    payment_elements_options.fields.billingDetails == undefined
                ) {
                    payment_elements_options.fields.billingDetails = {};
                }
                if (
                    payment_elements_options.fields.billingDetails.address ==
                    undefined
                ) {
                    payment_elements_options.fields.billingDetails.address = {};
                }
                payment_elements_options.fields.billingDetails.address.postalCode =
                    "never";
            }

            if (this.billing_address_country != "") {
                if (payment_elements_options.defaultValues == undefined) {
                    payment_elements_options.defaultValues = {};
                }
                if (
                    payment_elements_options.defaultValues.billingDetails ==
                    undefined
                ) {
                    payment_elements_options.defaultValues.billingDetails = {};
                }
                if (
                    payment_elements_options.defaultValues.billingDetails
                        .address == undefined
                ) {
                    payment_elements_options.defaultValues.billingDetails.address =
                        {};
                }
                payment_elements_options.defaultValues.billingDetails.address.country =
                    this.billing_address_country;

                if (payment_elements_options.fields == undefined) {
                    payment_elements_options.fields = {};
                }
                if (
                    payment_elements_options.fields.billingDetails == undefined
                ) {
                    payment_elements_options.fields.billingDetails = {};
                }
                if (
                    payment_elements_options.fields.billingDetails.address ==
                    undefined
                ) {
                    payment_elements_options.fields.billingDetails.address = {};
                }
                payment_elements_options.fields.billingDetails.address.country =
                    "never";
            }

            this.stripe_payment = this.stripe_elements.create(
                "payment",
                payment_elements_options
            );

            this.stripe_payment.mount(this.stripe_payment_element);

            this.stripe_payment.on("ready", () => {
                this.is_payment_element_ready = true;
                this.getCustomerCards();
            });

            this.stripe_payment.on("change", (event) => {
                this.is_payment_valid = event.complete;
            });
        }
    },
    template: `
    <div class="field__wrapper--card">
        <label class="field__title" v-if="customer_cards.length > 0">Saved Payment Methods</label>
        <div class="customer_cards_wrapper amex diners discover jcb maestro mastercard unionpay visa" v-if="customer_cards.length > 0">
            <div class="customer_card" :class="[customer_card.brand]" v-for="customer_card in customer_cards">
            <div class="customer_card__inner">
            <c-confirmation :trigger="customer_card.confirm_deletion" @open="customer_card.confirm_deletion = true" @close="customer_card.confirm_deletion = false" confirm="Delete" cancel="Cancel" @confirm="deleteCustomerCard(customer_card)">
                <h3>Delete Saved Card</h3><p>Are you sure you'd like to delete your saved card?</p>
            </c-confirmation>
            <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: customer_card.is_loading == true }">
                <div class="spinner"></div>
            </div>
            <div class="customer_card__dismiss" @click.stop="customer_card.confirm_deletion = true"></div><span class="customer_card__expiry"><small>Expiry Date:</small> {{ customer_card.expiry }}</span><span class="customer_card__number">{{ customer_card.number }}</span></div></div>
        </div>
        <div class="field__wrapper">
            <label class="field__title">Enter {{ (customer_cards.length > 0) ? "New " : "" }}Payment Method Details</label>
            <div class="stripe_payment_element"></div>
        </div>
        <a class="btn bg-primary border-primary text-primary_contrasting block" :class="{ is_disabled: is_disabled || !is_valid || !is_payment_valid }" @click.stop="submit">Add Card</a>
    </div>
    `,
};

export default _payment_stripe_cards;