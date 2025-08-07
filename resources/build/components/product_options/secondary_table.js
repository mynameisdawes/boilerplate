import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })
import { useVuelidate } from "@vuelidate/core";

import { default as c_input } from "../input.js";


let secondary_table = {
    name: "c-secondary_table",
    components: {
        "c-input": c_input
    },
    props: {
        propery_name: {
			type: String,
			default: "size"
		},
        option: {
            type: Object,
        },
        caption: {
			type: String,
			required: false
		},
		image: {
			type: String,
			required: false
		},
		preview: {
			type: String,
			required: false
		},
        variations: {
            type: Object
        },
        validate: {
            type: Array,
            default: false
        }
    },
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    data() {
        return {
        }
    },
    validations() {
        let validations = {
            validate: {
                validations: {
                    rules: {
                        valid: function(value) {
                            let valid = false;
                            value.every(val => {
                                if (val.qty != 0) {
                                    valid = true;
                                }
                                return !valid;
                            });
                            return valid;
                        }
                    }
                }
            }
        };

        if (this.validate) {
            return _validation.createFieldsValidationRules(validations);
        }

    },
    computed: {
		option_quantity() {
            return this.option[this.propery_name].reduce((acc, size) => acc + parseInt(size.qty), 0);
        },
	},
    template: `
    <div class="purchase-quantities-table">
        <article class="media">
            <figure v-if="image != null">
                <div class="media__image__content">
                    <img v-if="preview != null" :src="preview" :alt="option.label" width="160" height="160" />
                    <img v-if="image != null" :src="image" :alt="option.label" width="160" height="160" />
                </div>
            </figure>
            <header>{{ caption ?? option.label }}</header>
        </article>
        <table>
            <template v-for="opt_secondary in option[propery_name]">
                <template v-if="opt_secondary.is_enabled" v-for="var_secondary in variations">
                    <tr v-if="!var_secondary.disabled && opt_secondary.value == var_secondary.value">
                        <td class="font-bold">{{ var_secondary.label }}</td>
                        <td>
                            <div class="flex justify-end">
                                <c-input
                                type="number:buttons"
                                v-model="opt_secondary.qty"
                                :name="option.label + '_' + var_secondary.value"
                                ></c-input>
                            </div>
                        </td>
                    </tr>
                </template>
            </template>
            <tr>
                <td></td>
                <td>
                    <div class="table_total">
                        {{ option_quantity }}
                    </div>
                </td>
            </tr>
        </table>
	</div>
    `
}

export default secondary_table;
