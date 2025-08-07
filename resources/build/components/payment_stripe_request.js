import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";

let _payment_stripe_request = {
    name: "c-payment_stripe_request",
    emits: [
        "validate",
        "load",
        "unload",
        "success",
        "fail",
    ],
    props: {
        is_valid: {
            default: true
        },
        customer_id: {
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
            is_disabled: false,
            stripe: null,
            stripe_elements: null,
            stripe_request: null,
            stripe_request_element: null,
            empty_amount: "0.00",
            off_session: false,
        };
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
                data: response.error
            };
        },
        handlePaymentResponse(response, parent_result) {
            if (response.data.data != null && response.data.error == true) {
                if (typeof(parent_result) !== "undefined") {
                    parent_result.complete("fail");
                }
                this.is_disabled = false;
                this.$emit("fail", response);
            } else if (response.data.data != null && response.data.data.requires_action) {
                this.stripe
                    .handleCardAction(response.data.data.intent_client_secret)
                    .then((result) => {
                        if (result.error) {
                            if (typeof(parent_result) !== "undefined") {
                                parent_result.complete("fail");
                            }
                            this.is_disabled = false;
                            this.$emit("fail", this.transformErrorResponse(result));
                        } else {
                            let data = {
                                off_session: this.off_session,
                                amount: this.amount_pence,
                                amount_display: this.amount_display,
                                amount_pence: this.amount_pence,
                                amount_pounds: this.amount_pounds,
                                payment_intent_id: result.paymentIntent.id,
                                customer_id: response.data.data.customer
                            };

                            if (this.additional_data) {
                                Object.entries(this.additional_data).forEach(additional_data_item => {
                                    const [prop, value] = additional_data_item;
                                    if (typeof(data[prop]) === "undefined") {
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
                if (typeof(parent_result) !== "undefined") {
                    parent_result.complete("success");
                }
                this.off_session = false;
                this.is_disabled = false;
                this.$emit("success", response);
            }
        },
        submit(parent_result) {
            this.is_disabled = true;
            this.$emit("load");

            let data = {
                off_session: this.off_session,
                amount: this.amount_pence,
                amount_display: this.amount_display,
                amount_pence: this.amount_pence,
                amount_pounds: this.amount_pounds,
                payment_method_id: parent_result.paymentMethod.id
            };

            if (this.customer_id) {
                data.customer_id = this.customer_id;
            }

            if (this.additional_data) {
                Object.entries(this.additional_data).forEach(additional_data_item => {
                    const [prop, value] = additional_data_item;
                    if (typeof(data[prop]) === "undefined") {
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
                this.handlePaymentResponse(response, parent_result);
            })
            .catch((error) => {
                this.handlePaymentResponse(error.response, parent_result);
            });
        }
    },
    computed: {
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
        }
    },
    mounted() {
        if (typeof(Stripe) !== "undefined") {
            this.stripe_request_element = this.$el;

            if (typeof(this.stripe_request_element) !== "undefined") {
                let stripe_public_key = _config.get("payments.stripe.public_key");

                this.stripe = Stripe(stripe_public_key);

                this.stripe_elements = this.stripe.elements();

                let stripe_request_options = {
                    country: "GB",
                    currency: "gbp",
                    total: {
                        label: "Payment",
                        amount: 1,
                        pending: true
                    },
                    // requestPayerName: true,
                    // requestPayerEmail: true,
                };

                let stripe_payment_request = this.stripe.paymentRequest(stripe_request_options);

                let stripe_elements_options = {
                    paymentRequest: stripe_payment_request,
                };

                this.stripe_request = this.stripe_elements.create("paymentRequestButton", stripe_elements_options);

                stripe_payment_request.canMakePayment().then((result) => {
                    if (result) {
                        this.stripe_request.mount(this.stripe_request_element);
                        this.stripe_request_element.style.display = "block";
                        this.stripe_request.on("click", (e) => {
                            e.preventDefault();

                            this.$emit("validate");

                            if (this.is_disabled || !this.is_valid) {
                                return;
                            }

                            stripe_payment_request.update({
                                total: {
                                    label: "Payment",
                                    amount: parseFloat(this.amount_pence),
                                },
                            });

                            stripe_payment_request.show();
                        });
                    } else {
                        this.stripe_request_element.style.display = "none";
                    }
                });

                stripe_payment_request.on("paymentmethod", (result) => {
                    this.submit(result);
                });

                stripe_payment_request.on("cancel", () => {
                    this.$emit("unload");
                });
            }
        }
    },
    template: `
    <div class="btn__payment" :class="{ is_disabled: is_disabled || !is_valid }"></div>
    `
};

export default _payment_stripe_request;