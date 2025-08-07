import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })
import { useVuelidate } from "@vuelidate/core";

let _size_select = {
	name: "c-size_select",
	mixins: [_option_mixin],
	setup () {
		const v$ = useVuelidate({$autoDirty: true});
		return { v$ };
	},
	validations() {
		let validations = {
			validate: {
				validations: {
					rules: {
                        required: _validation.rules.required
					}
				}
			}
		};

		return _validation.createFieldsValidationRules(validations);
	},
	template: `
	<c-input :name="product.secondary" :label="product.variations[product.secondary].label" v-model="product.options[product.secondary]" :type="product.variationInputType(product.secondary)" :options="product.variations[product.secondary].variations"></c-input>
	`
}

export default _size_select;
