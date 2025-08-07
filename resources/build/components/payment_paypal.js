import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";

let _payment_paypal = {
    name: "c-payment_paypal",
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
            paypal: null,
            paypal_element: null,
            empty_amount: "0.00",
            actions: null
        };
    },
    watch: {
        is_valid: {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    this.validate_paypal();
                }
            },
            immediate: true
        }
    },
    methods: {
        validate_paypal() {
            if (this.actions) {
                if (
                    this.is_valid == true
                ) {
                    this.actions.enable();
                } else {
                    this.actions.disable();
                }
            }
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
        this.paypal_element = this.$el;

        if (typeof(this.paypal_element) !== "undefined") {

            paypal.Button.render({
                env: "production",
                commit: true,
                style: {
                    label: "generic",
                    size: "responsive",
                    shape: "rect",
                    color: "blue",
                    tagline: false
                },
                validate: (actions) => {
                    this.actions = actions;
                    this.validate_paypal();
                },
                onClick: () => {
                    this.$emit("validate");
                },
                payment: () => {
                    if (this.is_disabled || !this.is_valid) {
                        return;
                    }

                    this.is_disabled = true;
                    this.$emit("load");

                    let data = {
                        amount: this.amount_pounds,
                        amount_display: this.amount_display,
                        amount_pence: this.amount_pence,
                        amount_pounds: this.amount_pounds,
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

                    return _api
                        .request({
                            url: _config.get("api.payment_paypal.create"),
                            method: "post",
                            data: data
                        })
                        .then((response) => {
                            this.$emit("unload");
                            this.is_disabled = false;
                            if (
                                typeof(response.data.data) !==
                                "undefined" &&
                                typeof(response.data.data.state) !==
                                "undefined" &&
                                response.data.data.state == "created"
                            ) {
                                return response.data.data.payment_id;
                            }
                            return null;
                        })
                        .catch((error) => {
                            this.$emit("unload");
                            this.is_disabled = false;
                        });
                },
                onAuthorize: (payment_data, actions) => {
                    if (this.is_disabled || !this.is_valid) {
                        return;
                    }

                    this.is_disabled = true;
                    this.$emit("load");

                    let data = {
                        amount: this.amount_pounds,
                        amount_display: this.amount_display,
                        amount_pence: this.amount_pence,
                        amount_pounds: this.amount_pounds,
                        paymentID: payment_data.paymentID,
                        payerID: payment_data.payerID,
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

                    return _api
                        .request({
                            url: _config.get("api.payment_paypal.execute"),
                            method: "post",
                            data: data
                        })
                        .then((response) => {
                            if (
                                typeof(response.data.data) !==
                                "undefined" &&
                                typeof(response.data.data.state) !==
                                "undefined" &&
                                response.data.data.state == "approved"
                            ) {
                                this.is_disabled = false;
                                this.$emit("success", response);
                            } else {
                                this.is_disabled = false;
                                this.$emit("fail", response);
                            }
                        })
                        .catch((error) => {
                            this.is_disabled = false;
                            this.$emit("fail", error.response);
                        });
                }
            }, this.paypal_element);
        }
    },
    template: `
    <div class="btn__payment" :class="{ is_disabled: is_disabled || !is_valid }"></div>
    `
};

export default _payment_paypal;