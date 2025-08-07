@php
use Vektor\Utilities\Utilities;
$product_attrs_attrs = [];
if (!empty($ids)) {
    $product_attrs_attrs['interact_with_url'] = false;
}
$product_attrs_attrs['is_enabled'] = config('shop.filters.enabled');
if (isset($filters) && (bool) $filters == false) {
    $product_attrs_attrs['is_enabled'] = false;
}
if (isset($product_type)) {
    $product_attrs_attrs['is_enabled'] = true;
}
$product_attrs_props = '';
if (!empty($product_attrs_attrs)) {
    $product_attrs_props = ' ' . Utilities::arrayToVueAttributes($product_attrs_attrs);
}
$product_attrs = [];
if (!empty($ids)) { $product_attrs['ids'] = $ids; }
$product_attrs['filter'] = config('shop.filters.enabled');
if (isset($filters) && (bool) $filters == false) {
    $product_attrs['filter'] = false;
}
if (isset($product_type)) {
    $product_attrs['filter'] = true;
}
if (isset($paginate)) { $product_attrs['paginate'] = (bool) $paginate; }
if (isset($per_pages)) { $product_attrs['per_pages'] = $per_pages; }
$product_props = '';
if (!empty($product_attrs)) {
    $product_props = ' ' . Utilities::arrayToVueAttributes($product_attrs);
}
$product_pagination_attrs = [];
if (!empty($ids)) {
    $product_pagination_attrs['interact_with_url'] = false;
}
$product_pagination_props = '';
if (!empty($product_pagination_attrs)) {
    $product_pagination_props = ' ' . Utilities::arrayToVueAttributes($product_pagination_attrs);
}
@endphp
<c-product_attributes :attribute_names_excluded="['colour']"{!! $product_attrs_props !!}>
    <template v-slot:default="productAttributesScope">
        <c-products :filters="productAttributesScope.filters"{!! $product_props !!}>
            <template v-slot:default="productsScope">
                <div class="spinner__wrapper" :class="{ is_loading: productsScope.is_loading == true }">
                    <div class="spinner"></div>
                </div>
                <div class="products_wrapper<?php echo (isset($mode) && 'r' == $mode) ? ' products_wrapper--r' : ''; ?>">
                    <aside v-if="productAttributesScope.is_enabled && productAttributesScope.filter_fields" class="border-box filters">
                        <c-panel_expand :is_expanded="false" class="expand__panel--no_inner">
                            <template v-slot:methods_above="panelScope">
                                <header class="h4" @click="panelScope.toggle">Filters</header>
                            </template>
                            <template v-slot:default>
                                <div class="mt-6">
                                    <div v-for="filter in productAttributesScope.filter_fields">
                                        <template v-if="productAttributesScope.options?.[filter.name].length > 0">
                                            <label class="field__title">@{{ filter.name_label }}</label>
                                            <div class="field__collection x_scroll">
                                                <c-input class="radio__pill" :name="filter.name" v-model="filter.model" type="radio" value="" valuelabel="All"></c-input>
                                                <c-input class="radio__pill" :class="{'radio__pill--color': option.configuration && option.configuration.color ? true : false }" :style="{ '--radio_pill_color': option.configuration && option.configuration.color ? option.configuration.color : null }" :name="filter.name" v-model="filter.model" type="radio" :value="option.value" :valuelabel="option.text" v-for="(option, option_idx) in productAttributesScope.options?.[filter.name]"></c-input>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </template>
                        </c-panel_expand>
                    </aside>
                    <section class="products" v-if="productsScope.products_fetched == true && productsScope.products.length > 0">
                        <c-product :config="product" :key="product.id" v-for="(product, product_idx) in productsScope.products" @update:cart="updateCart"
                        @message:hide="hideMessage"
                        @message:success="successMessage"
                        @message:error="errorMessage">
                            <template v-slot:default="productScope">
                                <article class="product p-3 p-5:3 text-center flex flex-col">
                                    <a class="mb-4" :href="'{{ url('product') }}/' + product.slug">
                                        <div class="content__wrapper mb-4">
                                            <div class="content">
                                                <ul v-if="productScope.colours_array.length > 1" class="colour_options">
                                                    <template v-for="(colour, colour_idx) in productScope.colours_array">
                                                        <li v-if="colour_idx < productScope.colours_array_limit" :style="{'--colour_option': colour}"></li>
                                                    </template>
                                                    <li v-if="productScope.colours_array.length > productScope.colours_array_limit" class="colour_option_plus">+</li>
                                                </ul>
                                            </div>
                                            <div class="content">
                                                <span class="ml-auto badge:xs" v-if="productScope.config.configuration && productScope.config.configuration.gender">@{{ productScope.config.configuration.gender }}</span>
                                            </div>
                                        </div>
                                        <img loading="lazy" width="1500" height="1500" :src="productScope.image" :alt="productScope.config.name_label" />
                                    </a>
                                    <div class="mt-auto">
                                        <span class="block font-bold mb-3 text-balance" v-html="productScope.config.name_label"></span>
                                        <span v-if="!productScope.hide_pricing" class="block mb-4" v-html="productScope.formatPrice(productScope.display_price)"></span>
                                        <a :href="'{{ url('product') }}/' + product.slug" class="inline-grid w-full btn mb-2 text-xs uppercase py-3 font-bold bg-background hover:bg-primary hover:border-primary hover:text-primary_contrasting">View Product</a>
                                        <a @click.stop.prevent="productScope.openModal" class="block text-sm text-gray-400" v-if="productScope.config.configuration.is_multi_select == false && productScope.config.configuration.is_customisable == false">Quick View</a>
                                    </div>
                                    <c-modal :trigger="productScope.is_modal_shown" class="from_bottom" @open="productScope.openModal" @close="productScope.closeModal" v-if="productScope.config.configuration.is_multi_select == false && productScope.config.configuration.is_customisable == false">
                                        <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: productScope.is_loading == true }">
                                            <div class="spinner"></div>
                                        </div>
                                        <div class="product text-left mb-6 grid:2 grid-cols-3:2 gap-x-5:2">
                                            <div>
                                                <a :href="'{{ url('product') }}/' + product.slug">
                                                    <img loading="lazy" class="w-1/4:1e mx-auto:1e mb-4:1e" width="1500" height="1500" :src="productScope.image" :alt="productScope.config.name_label" />
                                                </a>
                                            </div>
                                            <div class="col-span-2:2">
                                                <div class="h2 font-bold" v-html="productScope.config.name_label"></div>
                                                <span class="badge mb-4" v-if="productScope.config.configuration.gender">@{{ productScope.config.configuration.gender }}</span>
                                                <template v-if="productScope.config.products.length > 0">
                                                    <hr />
                                                    <div class="variations py-4">
                                                        <c-input v-for="variation in productScope.variations" :name="variation.name" :label="variation.label" v-model="productScope.options[variation.name]" :type="productScope.variationInputType(variation.name)" :options="variation.variations"></c-input>
                                                    </div>
                                                    <hr />
                                                </template>
                                                <div class="mt-4 flex justify-between items-center">
                                                    <c-input v-if="productScope.config.qty_per_order != 1 && (productScope.config.qty_per_order_grouping != null && productScope.config.qty_per_order_grouping != 'id' && productScope.config.qty_per_order_grouping != 'sku')" name="qty" label="Qty" v-model="productScope.qty" @update:model-value="productScope.updateQty" type="number:buttons" class="sm"></c-input>
                                                    <span v-if="!productScope.hide_pricing" style="font-size: 1.6rem;" class="price font-bold">@{{ productScope.formatPrice(productScope.display_price) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <a v-if="productScope.multi_select == false && productScope.config.attributes.length == 0" class="btn block w-full bg-primary border-primary text-primary_contrasting" @click.stop="productScope.addCartItem" :disabled="productScope.is_disabled">Add to cart</a>
                                        <a v-else class="btn block w-full bg-primary border-primary text-primary_contrasting" :href="'{{ url('product') }}/' + product.slug">View product</a>
                                    </c-modal>
                                </article>
                            </template>
                        </c-product>
                    </section>
                    <c-message v-if="productsScope.products_fetched == true && productsScope.products.length == 0 && productAttributesScope.filters.length == 0" :required="true" content="There are no products yet" :trigger="true"></c-message>
                    <c-message v-if="productsScope.products_fetched == true && productsScope.products.length == 0 && productAttributesScope.filters.length > 0" :required="true" content="There are no products with this selection of filters" :trigger="true"></c-message>
                </div>
                <c-pagination v-show="productsScope.products_fetched == true && productsScope.products.length > 0 && productsScope.paginate === true" :properties="productsScope.pagination" :per_pages="productsScope.per_pages" @change-pagination="productsScope.changePaginationGetProducts"{!! $product_pagination_props !!}></c-pagination>
            </template>
        </c-products>
    </template>
</c-product_attributes>