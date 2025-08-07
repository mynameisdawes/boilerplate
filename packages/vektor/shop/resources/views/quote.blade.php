@extends('layouts.default')
@php
    $default_title = 'Preview a quote';
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
        <div class="container:xl">
            <c-onecrm_quote id="{{ $id }}">
                <template v-slot:default="quoteScope">
                    <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: quoteScope.is_loading == true }">
                        <div class="spinner"></div>
                    </div>
                    <div v-if="quoteScope.data_fetched == true && quoteScope.quote">
                        <div class="mb-8">
                            <h1>Your quote is ready.</h1>
                            <p class="text-xl">You're one step away from getting these into print. Please review your proofs and if everything's looking good, go ahead and confirm your order.</p>
                        </div>
                        <div class="flex:3 justify-between mb-8">
                            <div class="mb-4:1t2e">
                                <h2 class="mb-4">@{{ quoteScope.quote.number }}</h2>
                                <a href="{{ url("preview/{$id}?model=quote") }}" class="btn bg-primary border-primary text-primary_contrasting" target="_blank">View Proofs</a>
                            </div>
                            <div class="grid grid-cols-2 items-end text-sm:1e">
                                <div>
                                    <p class="mb-0">Valid Until:</p>
                                    <p class="mb-0">Est. Delivery:</p>
                                    <p class="mb-0">Quote Status:</p>
                                </div>
                                <div class="uppercase">
                                    <p class="mb-0 font-bold">@{{ quoteScope.quote.valid_until }}</p>
                                    <p class="mb-0 font-bold">5-7 working days</p>
                                    <p class="mb-0 font-bold">@{{ quoteScope.quote.status }}</p>
                                </div>
                            </div>
                        </div>
                        {{-- <input type="checkbox" v-model="quoteScope.quote.can_edit" />
                        <input type="checkbox" v-model="quoteScope.quote.enforce_minimum_quantities" /> --}}

                        <div class="mb-4" v-if="quoteScope.quote.can_edit">
                            <hr>
                            <h4 class="mt-4">Editing your quote</h4>
                            <p>Adjustments to the quantities can be performed@{{ quoteScope.quote.enforce_minimum_quantities ? ', provided that you meet or exceed the original quantities quoted' : '' }}. In the event that you would like to change your quote in a different way, please <a href="{{ route('checkout_quote.preview', $id) }}#contact_us">contact us.</a></p>
                            <hr>
                        </div>

                        <c-cart
                        :cart_instance="quoteScope.quote.id"
                        :can_edit="quoteScope.quote.can_edit"
                        :enforce_minimum_quantities="quoteScope.quote.can_edit && quoteScope.quote.enforce_minimum_quantities"
                        @update:cart="updateCart"
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
                                                        <header>@{{ item.formatted.name }}<template v-if="quoteScope.quote.can_edit && cartScope.enforce_minimum_quantities && cartScope.grouped.product_item_minimum_quantities[item_index] && cartScope.grouped.product_item_minimum_quantities[item_index] > 0"> (x@{{ cartScope.grouped.product_item_minimum_quantities[item_index] }})</template></header>
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
                                                                    <div class="item-customisation-image" v-if="item.options.colour">
                                                                        <template v-if="design.formatted.preview_url_start">
                                                                            <img width="800" height="800" :src="design.formatted.preview_url_start + '/' + item.options.colour + '/' + design.formatted.preview_url_end" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                                        </template>
                                                                        <template v-else>
                                                                            <img width="800" height="800" :src="design.preview" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                                            <img v-if="item.formatted.builder_images && item.formatted.builder_images[design_idx + '_image']" width="800" height="800" :src="item.formatted.builder_images[design_idx + '_image']" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                                            <img v-else width="800" height="800" :src="design.formatted.image" :alt="item.formatted.name + ' (' + design.formatted.label + ')'">
                                                                        </template>
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
                                                </div>
                                                <div class="item-qty">
                                                    <template v-if="item.attributes.multi_select">
                                                        <header>Quantities</header>
                                                        <ul v-if="cartScope.can_edit === true">
                                                            <c-form_segment :field_values="{ 'qty': item.qty, 'minimum_qty': cartScope.grouped.product_item_minimum_quantities[item_index] ? cartScope.grouped.product_item_minimum_quantities[item_index] : 0 }" :field_validation_rules="{ 'qty': cartScope.groupedProductMinimumQuantityValidation }">
                                                                <template v-slot:fields="quoteQuantitiesScope">
                                                                    <template v-for="size in item.attributes.sizes">
                                                                        <li v-if="size.is_enabled == true">
                                                                            <div class="flex items-center gap-3">
                                                                                <template v-for="attribute in size.formatted.attributes" v-if="size.formatted.attributes.length > 0">
                                                                                    <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                                                </template>
                                                                                <c-input :name="'qty[' + item_index + ']'" v-model="size.qty" type="number:buttons" class="sm" @blur="cartScope.updateCartItem(item, size)" @keyup.enter.prevent.stop="cartScope.updateCartItem(item, size)" :disable_buttons_minus="cartScope.enforce_minimum_quantities && item.qty < 2"></c-input>
                                                                            </div>
                                                                        </li>
                                                                    </template>
                                                                    <span class="field__message--error" v-if="cartScope.enforce_minimum_quantities && cartScope.grouped.product_item_minimum_quantities[item_index] && quoteQuantitiesScope.validation_rules.qty.$invalid">You must meet or exceed the original @{{ cartScope.grouped.product_item_minimum_quantities[item_index] }} quoted items to proceed</span>
                                                                </template>
                                                            </c-form_segment>
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
                                                            <c-form_segment :field_values="{ 'qty': item.qty, 'minimum_qty': cartScope.grouped.product_item_minimum_quantities[item_index] ? cartScope.grouped.product_item_minimum_quantities[item_index] : 0 }" :field_validation_rules="{ 'qty': cartScope.groupedProductMinimumQuantityValidation }">
                                                                <template v-slot:fields="quoteQuantitiesScope">
                                                                    <li>
                                                                        <div class="flex items-center gap-3">
                                                                            <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                                                <header v-if="attribute.name == 'size'">@{{ attribute.value_label }}</header>
                                                                            </template>
                                                                            <c-input :name="'qty[' + item_index + ']'" v-model="item.qty" type="number:buttons" class="sm" @blur="cartScope.updateCartItem(item)" @keyup.enter.prevent.stop="cartScope.updateCartItem(item)" :disable_buttons_minus="cartScope.enforce_minimum_quantities && item.qty < 2"></c-input>
                                                                        </div>
                                                                    </li>
                                                                    <span class="field__message--error" v-if="cartScope.enforce_minimum_quantities && cartScope.grouped.product_item_minimum_quantities[item_index] && quoteQuantitiesScope.validation_rules.qty.$invalid">You must meet or exceed the original @{{ cartScope.grouped.product_item_minimum_quantities[item_index] }} quoted items to proceed</span>
                                                                </template>
                                                            </c-form_segment>
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
                                            </li>
                                        </ul>
                                        <div class="cart-totals">
                                            <div class="cart-subtotal"><header>Subtotal</header><p>@{{ cartScope.formatted.product_subtotal }}</p></div>
                                            <div v-if="cartScope.shipping_count > 0" class="cart-shipping"><header>Shipping</header><p>@{{ cartScope.formatted.shipping_subtotal }}</p></div>
                                            <div class="cart-tax"><header>Tax</header><p>@{{ cartScope.formatted.tax }}</p></div>
                                            <div class="cart-total"><header>Total</header><p>@{{ cartScope.formatted.total }}</p></div>
                                        </div>

                                        <div class="mt-6">
                                            <div class="mb-4" v-if="quoteScope.quote.low_res_artwork_provided">
                                                <h4>Low resolution proof</h4>
                                                <p>Please note that we have proofed this with low resolution artwork for design and quoting purposes. If you wanted go ahead with the quote, we will make sure that high resolution artwork is supplied and used before printing.</p>
                                            </div>
                                            <div class="mb-4">
                                                <h4>Before you approve</h4>
                                                <p>If you would like to go ahead with this order, please check your proofs carefully before approving. It will not be possible to make alterations to the design once the order has been confirmed.</p>
                                            </div>
                                            <div id="contact_us">
                                                <h4>Need to talk to us?</h4>
                                                <p>Please contact us before confirming this order if you have any questions or changes.<br />
                                                    <a class="font-bold no-underline" href="tel:+441444657399">01444 657 399</a> (Mon-Fri 08:00-16:30) | <a class="font-bold no-underline" href="mailto:print@vektor.co.uk">print@vektor.co.uk</a>
                                                </p>
                                            </div>
                                        </div>

                                        <div class="mt-4">
                                            <c-input class="pt-3 mb-2 uppercase font-bold" label="Approve Proofs" name="confirm_proofs" type="switch" valuelabel="Approve Proofs" v-model="quoteScope.proofs.confirmed"></c-input>
                                            <p>I confirm that I am satisfied with the proofs and wish to continue.</p>
                                            <button :disabled="!cartScope.can_checkout || !quoteScope.proofs.confirmed" class="btn bg-primary border-primary text-primary_contrasting" @click.prevent="cartScope.quote_checkout(quoteScope.quote.id)">Confirm Order</button>
                                        </div>
                                    </template>
                                    <template v-if="cartScope.product_count == 0 && cartScope.cart_fetched == true">
                                        <p>You have no items in your quote.</p>
                                    </template>
                                </div>
                            </template>
                        </c-cart>
                    </div>
                    <div v-if="quoteScope.already_converted">
                        <h1>Whoops...</h1>
                        <p>Looks like you've already approved this quote.</p>
                    </div>
                </template>
            </c-onecrm_quote>
        </div>
    </div>
@endsection