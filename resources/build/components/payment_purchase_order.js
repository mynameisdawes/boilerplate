import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";

import { ref } from "vue";
import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

import c_form from "./form.js";
import c_input from "./input.js";

let amount_to_compare = ref(null);

let fields = {
    purchase_order_amount: {
        validations: {
            rules: {
                required: _validation.rules.required,
                greaterThanAmount: _validation_helpers.withParams(
                    { type: "greaterThanAmount" },
                    function(value, context) {
                        if (value == "") {
                            return true;
                        } else {
                            if (parseFloat(value) >= amount_to_compare.value) {
                                return true;
                            }
                            return false;
                        }
                    }
                )
            },
            messages: {
                greaterThanAmount: "The purchase order amount must equal or exceed the order total"
            }
        },
    },
    purchase_order_number: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    purchase_order_file: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
};

let _payment_purchase_order = {
    name: "c-payment_purchase_order",
    components: {
        "c-form": c_form,
        "c-input": c_input,
    },
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
            hide_pricing: _config.get("shop.hide_pricing"),
            cash_element: null,
            empty_amount: "0.00",
            forms: {
                purchase_order: {
                    ref: "purchase_order",
                    field_values: _validation.createFieldsData(fields),
                    field_storage: _validation.createFieldsStorage(fields),
                    validation_rules: _validation.createFieldsValidationRules(fields),
                    validation_messages: _validation.createFieldsValidationMessages(fields),
                }
            },
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
                purchase_order_amount: this.forms.purchase_order.field_values.purchase_order_amount,
                purchase_order_number: this.forms.purchase_order.field_values.purchase_order_number,
                purchase_order_file: this.forms.purchase_order.field_values.purchase_order_file,
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
                url: _config.get("api.payment_purchase_order.pay"),
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
    watch: {
        amount: {
            handler(new_val) {
                amount_to_compare.value = parseFloat(new_val);
            },
            immediate: true
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
            return "Checkout with Purchase Order";
        }
    },
    template: `
    <c-form :name="forms.purchase_order.ref" :ref="forms.purchase_order.ref" method="post" :action="forms.purchase_order.action" :field_values="forms.purchase_order.field_values" :field_storage="forms.purchase_order.field_storage" :field_validation_rules="forms.purchase_order.validation_rules" :field_validation_messages="forms.purchase_order.validation_messages" :clear_fields="true">
        <template v-slot:fields="form">
            <c-input name="purchase_order_amount" v-model="form.field_values.purchase_order_amount" :validationrule="form.validation_rules.purchase_order_amount" :validationmsg="form.validation_messages.purchase_order_amount" label="Purchase Order Amount" type="number"></c-input>
            <c-input name="purchase_order_number" v-model="form.field_values.purchase_order_number" :validationrule="form.validation_rules.purchase_order_number" :validationmsg="form.validation_messages.purchase_order_number" label="Purchase Order Number"></c-input>
            <c-input name="purchase_order_file" v-model="form.field_values.purchase_order_file" :validationrule="form.validation_rules.purchase_order_file" :validationmsg="form.validation_messages.purchase_order_file" label="Purchase Order PDF" type="file" endpoint="upload" accept="application/pdf, application/octet-stream" :preview="false"></c-input>
            <button class="btn btn__payment bg-primary border-primary text-primary_contrasting block" :class="{ is_disabled: is_disabled || !is_valid }" @click.prevent.stop="submit">{{ button_text }}</button>
        </template>
    </c-form>
    `
};

export default _payment_purchase_order;