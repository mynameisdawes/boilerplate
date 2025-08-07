@extends('layouts.default')
@php
    $default_title = 'Cart';
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content')
    <div class="container-gutter:outer">
        <div class="document__header__actions">
            <div class="document__navigation_strip">
                <div class="container:xl">
                    <div class="content__wrapper">
                        <div class="content">
                            <ul class="breadcrumbs">
                                <li><a href="{{ route('shop.product.index') }}">Back to Shop</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container:xl">
            <h1 class="text-gradient">Shopping cart</h1>
            <c-message :content="success_message" class="message--positive message--top" :trigger="is_success_message_shown" :autohide="true"></c-message>
            <c-message :content="error_message" class="message--negative message--top" :trigger="is_error_message_shown" :autohide="true"></c-message>
            <c-cart
            @update:cart="updateCartCount"
            @message:hide="hideMessage"
            @message:success="successMessage"
            @message:error="errorMessage">
                <template v-slot:default="cartScope">
                    <div class="cart">
                        <div class="spinner__wrapper" :class="{ is_loading: cartScope.is_loading == true }">
                            <div class="spinner"></div>
                        </div>
                        <template v-if="cartScope.product_count > 0 && cartScope.cart_fetched == true">
                            <ul class="cart-items">
                                <li class="cart-item" v-for="(item, item_index) in cartScope.grouped.product_items">
                                    <div class="item-image">
                                        <a :href="item.formatted.url">
                                            <img width="800" height="800" :src="item.formatted.images[0]" :alt="item.formatted.name">
                                        </a>
                                    </div>
                                    <div class="item-name">
                                        <a :href="item.formatted.url" >
                                            <header>@{{ item.formatted.name }}</header>
                                            <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                <p v-if="attribute.name == 'colour'">(@{{ attribute.value_label }})</p>
                                            </template>
                                            <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                <template v-if="attribute.value == 'text' && attribute.value_label">
                                                    <p><em><u>@{{ attribute.name_label }}:</u> @{{ attribute.value_label }}</em></p>
                                                </template>
                                            </template>
                                            <template v-for="(option, option_name) in item.options">
                                                <template v-if="option_name == 'note' && option && option != ''">
                                                    <p><em><u>Note:</u> @{{ option }}</em></p>
                                                </template>
                                            </template>
                                        </a>
                                        <c-panel_expand :is_expanded="false" v-if="item.options.customisation_id && cartScope.customisations[item.options.customisation_id]" class="expand__panel--no_inner">
                                            <template v-slot:methods_above="panelScope">
                                                <a class="item-details-toggle" @click="panelScope.toggle">@{{ panelScope.is_expanded ? 'Hide' : 'Show'}} Customisation Details</a>
                                            </template>
                                            <template v-slot:default>
                                                <ul class="item-customisations">
                                                    <li v-for="(design, design_idx) in cartScope.customisations[item.options.customisation_id].designs">
                                                        <div class="item-customisation-image">
                                                            <img width="800" height="800" :src="design.preview" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                            <img v-if="item.formatted.builder_images && item.formatted.builder_images[design_idx + '_image']" width="800" height="800" :src="item.formatted.builder_images[design_idx + '_image']" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                            <img v-else width="800" height="800" :src="design.formatted.image" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                        </div>
                                                        <div class="item-customisation-details">
                                                            <p><u>@{{ design.formatted.title }}</u></p>
                                                            <p><strong>Position:</strong> @{{ design.formatted.label }}</p>
                                                            <p>@{{ design.formatted.web }}<br />@{{ design.formatted.print }}</p>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </template>
                                        </c-panel_expand>
                                    </div>
                                    <div class="item-price" v-if="cartScope.hide_pricing === false">
                                        <header>Unit Price</header>
                                        <p>@{{ item.formatted.display_price }}</p>
                                        @if (Auth::check() && data_get(Auth::user(), 'configuration.can_customise_prices', false))
                                            <c-input class="item-price-custom" name="custom_price" v-model="item.custom_price" type="number" label="Custom Price (excl. VAT)" @blur="cartScope.updateCartItem(item)" @keyup.enter.prevent.stop="cartScope.updateCartItem(item)"></c-input>
                                        @endif
                                    </div>
                                    <div class="item-qty">
                                        <template v-if="item.attributes.multi_select">
                                            <header>Quantities</header>
                                            <ul v-if="cartScope.can_edit === true">
                                                <template v-for="size in item.attributes.sizes">
                                                    <li v-if="size.is_enabled == true">
                                                        <div class="flex items-center gap-3">
                                                            <template v-for="attribute in size.formatted.attributes" v-if="size.formatted.attributes.length > 0">
                                                                <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                            </template>
                                                            <c-input :name="'qty[' + item_index + ']'" v-model="size.qty" type="number:buttons" class="sm" @blur="cartScope.updateCartItem(item, size)" @keyup.enter.prevent.stop="cartScope.updateCartItem(item, size)"></c-input>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                            <ul v-else>
                                                <template v-for="size in item.attributes.sizes">
                                                    <li v-if="size.qty > 0">
                                                        <div class="flex items-center gap-3">
                                                            <template v-for="attribute in size.formatted.attributes" v-if="size.formatted.attributes.length > 0">
                                                                <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                            </template>
                                                            <p>@{{ size.qty }}</p>
                                                        </div>
                                                    </li>
                                                </template>
                                            </ul>
                                        </template>
                                        <template v-else>
                                            <header>Quantity</header>
                                            <ul v-if="cartScope.can_edit === true">
                                                <li>
                                                    <div class="flex items-center gap-3">
                                                        <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                            <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                        </template>
                                                        <c-input :name="'qty[' + item_index + ']'" v-model="item.qty" type="number:buttons" class="sm" @blur="cartScope.updateCartItem(item)" @keyup.enter.prevent.stop="cartScope.updateCartItem(item)"></c-input>
                                                    </div>
                                                </li>
                                            </ul>
                                            <ul v-else>
                                                <li>
                                                    <div class="flex items-center gap-3">
                                                        <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                            <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                        </template>
                                                        <p>@{{ item.qty }}</p>
                                                    </div>
                                                </li>
                                            </ul>
                                        </template>
                                    </div>
                                    <div class="item-subtotal" v-if="cartScope.hide_pricing === false">
                                        <header>Subtotal</header>
                                        <p>@{{ item.formatted.display_subtotal }}</p>
                                    </div>
                                    <div class="item-actions">
                                        <a href="#" class="btn__remove" @click.prevent="cartScope.deleteCartItem(item)" title="Remove"></a>
                                    </div>
                                </li>
                            </ul>
                            <div class="cart-discount_code">
                                <c-form
                                    name="cart_discount"
                                    :ref="cartScope.setDiscountRef"
                                    method="post"
                                    :field_values="cartScope.forms.discount.field_values"
                                    :field_storage="cartScope.forms.discount.field_storage" :field_validation_rules="cartScope.forms.discount.validation_rules" :field_validation_messages="cartScope.forms.discount.validation_messages"
                                >
                                    <template v-slot:fields="form">
                                        <label class="field__title">Discount Code</label>
                                        <c-message :content="cartScope.discount_error_message" class="message--negative" :trigger="cartScope.is_discount_error_message_shown" :autohide="true"></c-message>
                                        <c-message :content="cartScope.discount_success_message" class="message--positive" :trigger="cartScope.is_discount_success_message_shown" :autohide="true"></c-message>
                                        <div class="discount_wrapper">
                                            <c-input name="discount_code" label="Discount Code" v-model="form.field_values.discount_code" :validationrule="form.validation_rules.discount_code" :validationmsg="form.validation_messages.discount_code" autocomplete="off" :disabled="cartScope.discount > 0"></c-input>
                                            <button v-if="cartScope.discount > 0" @click.prevent="cartScope.cancelDiscountCode()" class="btn bg-secondary border-secondary text-primary_contrasting">Cancel</button>
                                            <button v-else @click.prevent="cartScope.applyDiscountCode()" class="btn bg-secondary border-secondary text-primary_contrasting">Apply</button>
                                        </div>
                                    </template>
                                </c-form>
                            </div>
                            <div class="cart-totals" v-if="cartScope.hide_pricing === false">
                                <div class="cart-subtotal"><header>Subtotal</header><p>@{{ cartScope.formatted.product_subtotal }}</p></div>
                                <div v-if="cartScope.shipping_count > 0" class="cart-shipping"><header>Shipping <span class="text-xs">(net)</span></header><p>@{{ cartScope.formatted.shipping_subtotal }}</p></div>
                                <div v-if="cartScope.discount > 0" class="cart-discount"><header>Discount</header><p>-@{{ cartScope.formatted.discount }}</p></div>
                                <div class="cart-tax"><header>Tax</header><p>@{{ cartScope.formatted.tax }}</p></div>
                                <div class="cart-total"><header>Total</header><p>@{{ cartScope.formatted.total }}</p></div>
                            </div>

                            <div class="cart-actions">
                                <button class="btn border-transparent text-sm underline" @click.prevent="cartScope.openCartSave" v-if="cartScope.is_logged_in">Save Cart</button>
                                <button class="btn border-transparent text-sm underline" @click.prevent="cartScope.clearCart">Clear Cart</button>
                                <button class="btn bg-primary border-primary text-primary_contrasting" @click.prevent="cartScope.checkout">Checkout</button>
                            </div>
                        </template>
                        <template v-if="cartScope.product_count == 0 && cartScope.cart_fetched == true">
                            <p>You have no items in your shopping cart.</p>
                            <p>
                                <a href="{{ route('shop.product.index') }}" class="btn bg-primary border-primary text-primary_contrasting">Back to Shop</a>
                            </p>
                        </template>
                    </div>

                    <c-modal :trigger="cartScope.forms.cart_save.show_modal" @open="cartScope.openCartSave" @close="cartScope.closeCartSave">
                        <h3>Save cart for later</h3>
                        <c-form
                            name="cart_save"
                            :ref="cartScope.setCartSaveRef"
                            method="post"
                            :field_values="cartScope.forms.cart_save.field_values"
                            :field_storage="cartScope.forms.cart_save.field_storage" :field_validation_rules="cartScope.forms.cart_save.validation_rules" :field_validation_messages="cartScope.forms.cart_save.validation_messages"
                        >
                            <template v-slot:fields="form">
                                <c-input name="cart_name" v-model="form.field_values.name" :validationrule="form.validation_rules.name" :validationmsg="form.validation_messages.name" label="Cart Name"></c-input>
                                <button class="btn bg-primary border-primary text-primary_contrasting" @click.prevent="cartScope.saveCart" :class="{ is_disabled: form.v$.$invalid == true }">Save</button>
                            </template>
                        </c-form>
                    </c-modal>

                    <div class="saved_carts" v-if="cartScope.saved_carts.length > 0">
                        <h2 class="h3">Saved Carts</h2>
                        <ul>
                            <li v-for="saved_cart in cartScope.saved_carts" @click.prevent="cartScope.restoreCart(saved_cart.identifier)">
                                @{{saved_cart.name ? saved_cart.name : 'Cart' }}<br /><small><em>(Saved at @{{ saved_cart.saved_at }})</em></small>
                                <div class="btn btn:sm bg-secondary border-secondary text-secondary_contrasting">Restore</div>
                            </li>
                        </ul>
                    </div>
                </template>
            </c-cart>
        </div>
    </div>
@endsection