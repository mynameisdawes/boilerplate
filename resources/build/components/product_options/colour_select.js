import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _colour_select = {
    name: "c-colour_select",
    mixins: [_option_mixin],
    data() {
        return {
        }
    },
    template: `
    <div class="mb-2 pt-4:3 mx-auto colour__selection order-1:1t2e" :class="{ 'hidden:1t2e': product.getActiveStep() != 1, 'pb-4:3': product.panels.colour_select.is_expanded }">
        <h3 class="expand__trigger mb-2:1t2e font-normal" :class="{ 'expand__trigger--expanded':  product.panels.colour_select.is_expanded }">
			<div class="flex items-center justify-between pr-6" @click="product.panels.colour_select.is_expanded = !product.panels.colour_select.is_expanded">
                <div class="flex items-center gap-3">
                    <span>Select your colour{{ product.multi_select ? 's' : '' }}</span>
                    <c-tooltip><p>You'll be able to choose colours all the way through the design process.</p></c-tooltip>
                </div>
				<span class="flex gap-1">
					<input v-for="(option, idx) in product.selected_sorted" v-show="idx < 4" type="radio" class="radio color dummy" :name="'dummy_' + option.label" checked="true" :style="{ 'background-color': option.hex }" disabled="true" aria-hidden="true">
				</span>
			</div>
		</h3>
        <c-panel_expand class="expand__panel--no_inner" :is_expanded="product.panels.colour_select.is_expanded">
            <c-input v-if="product.multi_select" class="pb-2:1t2e" :name="product.variations[product.primary].name" :label="product.variations[product.primary].label" v-model="product.options.selected" :type="product.variationInputType(product.variations[product.primary].name)" :options="product.configurations" :allow_null="false"></c-input>
            <c-input v-else class="pb-2:1t2e" :name="product.variations[product.primary].name" :label="product.variations[product.primary].label" v-model="product.options[product.primary]" :type="product.variationInputType(product.variations[product.primary].name)" :options="product.variations[product.primary].variations" :allow_null="false"></c-input>
        </c-panel_expand>
    </div>
    `
}

export default _colour_select;
