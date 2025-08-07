import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";

let _checkout_quote = {
    name: "c-checkout_quote",
    props: {
        is_valid: {
            default: true
        },
        additional_data: {
            default: null
        },
        amount: {
            required: true
        },
        redirect_url: {
            default: null
        }
    },
    data() {
        return {
            is_disabled: false,
            hide_pricing: _config.get("checkout.hide_pricing"),
            quote_element: null,
            empty_amount: "0.00",
        };
    },
    methods: {
        submit() {
            this.$emit("validate");

            if (this.is_disabled || !this.is_valid) {
                return;
            }

            this.is_disabled = true;
            this.$emit("load");

            let data = {
                amount: this.amount_pence,
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

            _api.request({
                url: _config.get("api.checkout_quote.create"),
                method: "post",
                data: data
            })
            .then((response) => {
                this.is_disabled = false;
                this.$emit("success", response);
            })
            .catch((error) => {
                this.is_disabled = false;
                this.$emit("fail", error.response);
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
        },
        button_text() {
            return (this.hide_pricing == true) ? "Create Quote" : "Create Quote for £" + this.amount_pounds;
        }
    },
    template: `
    <a class="btn btn__payment bg-primary border-primary text-primary_contrasting block" :class="{ is_disabled: is_disabled || !is_valid }" @click.stop="submit">{{ button_text }}</a>
    `
};

export default _checkout_quote;