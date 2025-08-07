<div class="variations variations-center:1t2e py-4 max-w-md:1t2e mx-auto">
    <c-input v-if="!productScope.multi_select" v-for="variation in productScope.variations" :name="variation.name" :label="variation.label" v-model="productScope.options[variation.name]" :type="productScope.variationInputType(variation.name)" :options="variation.variations"></c-input>
    <template v-else>
        <c-input :name="productScope.primary" :label="productScope.variations[productScope.primary].label" v-model="productScope.options.selected" :type="productScope.variationInputType(productScope.primary)" :options="productScope.configurations" :allow_null="false"></c-input>
        <div class="mb-6:1t2e">
            <c-size_multi_select :can_remove="productScope.selected_sorted.length > 1" :product="productScope" @option_removed="productScope.removeOption"></c-size_multi_select>
        </div>
        <button @click.stop="productScope.addCartItem" :class="{ is_disabled: productScope.is_disabled }" class="btn bg-primary text-primary_contrasting hidden">Add To Cart</button>
    </template>
</div>
<div class="attributes attributes-center:1t2e py-4 max-w-md:1t2e mx-auto" v-if="productScope.config.attributes.length > 0">
    <c-input v-for="(attribute, attributes_idx) in productScope.config.attributes" :type="attribute.value" :name="attribute.name" v-model="productScope.options[attribute.name]" :label="attribute.name_label" :validationrule="productScope.validation_rules.options[attribute.name]"></c-input>
</div>
<span v-if="!productScope.hide_pricing" style="font-size: 2rem;" class="price font-bold block mt-1 mb-4 text-center:1t2e">@{{ productScope.formatPrice(productScope.display_price) }}</span>
<div class="flex flex-wrap gap-6 items-end justify-center:1t2e mt-4 mb-8">
    <c-input v-if="!productScope.multi_select && productScope.config.qty_per_order != 1 && (productScope.config.qty_per_order_grouping != 'id' && productScope.config.qty_per_order_grouping != 'sku')" name="qty" label="Qty" v-model="productScope.qty" @update:model-value="productScope.updateQty" type="number:buttons" class="mb-0"></c-input>
    <a class="btn block bg-primary border-primary text-primary_contrasting" @click.stop="productScope.addCartItem" :disabled="productScope.is_disabled">Add to Cart</a>
</div>