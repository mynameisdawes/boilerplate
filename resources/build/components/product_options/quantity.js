import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })
import { useVuelidate } from "@vuelidate/core";

let _quantity = {
	name: "c-quantity",
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
                        minValue: _validation.rules.minValue(1)
					}
				}
			}
		};

		return _validation.createFieldsValidationRules(validations);
	},
	template: `
    <c-input v-if="product.config.qty_per_order != 1 && (product.config.qty_per_order_grouping != 'id' && product.config.qty_per_order_grouping != 'sku')" name="qty" label="Qty" v-model="product.qty" @update:model-value="product.updateQty" type="number:buttons" class="mb-0 mr-8"></c-input>
	`
}

export default _quantity;
