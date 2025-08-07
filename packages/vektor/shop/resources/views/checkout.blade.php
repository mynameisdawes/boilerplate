@extends('layouts.default')
@php
    $default_title = 'Checkout';
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
                                @if (isset($model) && isset($instance))
                                    <li><a href="{{ url($model . "/" . $instance . '?return_from=checkout') }}">Back to {{ ucfirst($model) }}</a></li>
                                @else
                                    <li><a href="{{ route('shop.cart.index') }}">Back to Cart</a></li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container:xl">
            @if (isset($model) && isset($instance))
            <c-checkout
            cart_instance="{{ $instance ?? null }}"
            from_model="{{ $model ?? null }}"
            :email_match_auth="true"
            :ref_name="'{{ $instance ?? 'quote' }}_checkout'"
            @update:cart="updateCartCount" @authentication="authentication">
            @else
            <c-checkout
            :prop_quote_only="'{{ config('onecrm.enabled') && !isset($instance) && Auth::check() && data_get(Auth::user(), 'configuration.can_create_quotes', false) }}' ? true : false"
            @update:cart="updateCartCount" @authentication="authentication">
            @endif
                <template v-slot:default="checkoutScope">
                    <template v-if="checkoutScope.product_count > 0">
                        <h1>Checkout</h1>

                        <c-message :content="checkoutScope.error_message" class="message--negative" :trigger="checkoutScope.is_error_message_shown" :required="true"></c-message>

                        <div class="border-box p-5 p-0:1t2e mb-6">
                            <table class="table--responsive:1t2e p-5:1t2e">
                                <thead>
                                    <tr class="text-sm">
                                        <th>Name</th>
                                        <th v-if="!checkoutScope.hide_pricing">Price</th>
                                        <th>Qty</th>
                                        <th v-if="!checkoutScope.hide_pricing">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-sm" v-for="(item, item_index) in checkoutScope.grouped.product_items">
                                        <td data-header="Name">
                                            <strong>@{{ item.formatted.name }}</strong>
                                            <template v-if="item.attributes.multi_select && item.attributes.multi_select == true">
                                                <template v-for="(size, size_idx) in item.attributes.sizes" v-if="item.attributes.sizes.length > 0">
                                                    <template v-if="size_idx == 0">
                                                        <template v-for="attribute in size.formatted.attributes" v-if="size.formatted.attributes.length > 0"><span v-if="attribute.name == 'colour'"> (@{{ attribute.value_label }})</span></template>
                                                    </template>
                                                </template>
                                                <template v-for="(size, size_idx) in item.attributes.sizes" v-if="item.attributes.sizes.length > 0">
                                                    <template v-for="attribute in size.formatted.attributes" v-if="size.qty > 0 && size.formatted.attributes.length > 0">
                                                        <span v-if="attribute.name == 'size'">@{{ size_idx == 0 ? ' - ' : ', ' }}<strong>@{{ attribute.value_label }}</strong></span>
                                                    </template>
                                                </template>
                                            </template>
                                            <template v-else>
                                                <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0"><span v-if="attribute.name == 'colour'"> (@{{ attribute.value_label }})</span></template><template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0"><span v-if="attribute.name == 'size'"> - <strong>@{{ attribute.value_label }}</strong></span></template>
                                            </template>
                                            <template v-for="attribute in item.formatted.attributes" v-if="item.formatted.attributes.length > 0">
                                                <template v-if="attribute.value == 'text' && item.options[attribute.name]">
                                                    <br /><span><em><u>@{{ attribute.name_label }}:</u> @{{ item.options[attribute.name] }}</em></span>
                                                </template>
                                            </template>
                                            <template v-for="(option, option_name) in item.options">
                                                <template v-if="option_name == 'note' && option && option != ''">
                                                    <br /><span><em><u>Note:</u> @{{ option }}</em></span>
                                                </template>
                                            </template>
                                        </td>
                                        <td v-if="!checkoutScope.hide_pricing" data-header="Price">@{{ checkoutScope.formatPrice(item.display_price) }}</td>
                                        <td data-header="Qty">@{{ item.qty }}</td>
                                        <td v-if="!checkoutScope.hide_pricing" data-header="Subtotal">@{{ checkoutScope.formatPrice(item.display_price * item.qty) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        @if (!isset($instance) && !isset($model))
                            @if (config('onecrm.enabled') && Auth::check() && data_get(Auth::user(), 'configuration.can_create_quotes', false))
                                <c-input label="Checkout type" name="quote_only" type="switch" valuelabel="Create quote only" v-model="checkoutScope.options.quote_only"></c-input>
                            @endif
                        @endif
                        <c-form
                            :name="checkoutScope.ref_name"
                            :ref="checkoutScope.ref_name"
                            method="post"
                            :action="checkoutScope.action"
                            :field_values="checkoutScope.field_values"
                            :field_storage="checkoutScope.field_storage" :field_validation_rules="checkoutScope.validation_rules" :field_validation_messages="checkoutScope.validation_messages"
                        >
                            <template v-slot:fields="form">
                                <div class="mb-6">
                                    <div class="mb-4">
                                        <h3>@{{ checkoutScope.options.quote_only ? "Customer" : "Personal" }} details</h3>
                                    </div>
                                    <c-message content="You are now logged in." :trigger="checkoutScope.is_logged_in === true" :required="false"></c-message>
                                    <div class="grid:3 grid-cols-2:3 gap-x-4:3">
                                        <c-input name="first_name" label="First Name" v-model="form.field_values.first_name" :validationrule="form.validation_rules.first_name" :validationmsg="form.validation_messages.first_name" autocomplete="given-name" :disabled="checkoutScope.is_logged_in === true"></c-input>
                                        <c-input name="last_name" v-model="form.field_values.last_name" :validationrule="form.validation_rules.last_name" :validationmsg="form.validation_messages.last_name" label="Last Name" autocomplete="family-name" :disabled="checkoutScope.is_logged_in === true"></c-input>
                                        <c-input class="mb-0:3" name="phone" type="tel" v-model="form.field_values.phone" :validationrule="form.validation_rules.phone" :validationmsg="form.validation_messages.phone" label="Phone Number" autocomplete="tel"></c-input>
                                        <c-input name="email" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" label="Email Address" autocomplete="email" type="email" :disabled="checkoutScope.is_logged_in === true"></c-input>
                                        <template v-if="checkoutScope.is_logged_in === false">
                                            <template v-if="checkoutScope.exists === false">
                                                <div class="col-span-2:3 mb-4">
                                                    <c-message class="message--warning" content="Please enter a password below to create an account." :trigger="true" :required="true"></c-message>
                                                </div>
                                                <c-input class="mb-0:3" name="password" type="password" label="Password" placeholder="Enter password" v-model="form.field_values.password" :validationrule="form.validation_rules.password" :validationmsg="form.validation_messages.password" autocomplete="new-password"></c-input>
                                                <c-input class="mb-0" name="password_confirmation" type="password" label="Confirmation Password" placeholder="Confirmation password" v-model="form.field_values.password_confirmation" :validationrule="form.validation_rules.password_confirmation" :validationmsg="form.validation_messages.password_confirmation" autocomplete="new-password"></c-input>
                                            </template>
                                            <template v-if="checkoutScope.exists === true">
                                                <div class="col-span-2:3 bg-background border-box p-8 p-10:3 relative">
                                                    <c-message class="message--warning" content="Please enter your existing password below to log in and proceed." :trigger="true" :required="true"></c-message>
                                                    <c-input name="email" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" label="Email Address" autocomplete="email" type="email"></c-input>
                                                    <c-input name="password" type="password" label="Password" placeholder="Enter password" v-model="form.field_values.password" :validationrule="form.validation_rules.password" :validationmsg="form.validation_messages.password" autocomplete="current-password"></c-input>

                                                    <c-message :content="checkoutScope.auth_error_message" class="message--negative" :trigger="checkoutScope.is_auth_error_message_shown"></c-message>
                                                    <c-message :content="checkoutScope.auth_success_message" class="message--positive" :trigger="checkoutScope.is_auth_success_message_shown"></c-message>

                                                    <button @click.prevent="checkoutScope.submitUserPassword(form)" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.validation_rules.email.$invalid == true || form.validation_rules.password.$invalid == true }">Login</button>
                                                    <div class="mt-4 text-sm">
                                                        <a class="forgot_password" href="{{ route('password.request') }}" target="_blank">Forgot Your Password?</a>
                                                    </div>
                                                </div>
                                            </template>
                                        </template>
                                    </div>
                                </div>
                                <template v-if="!checkoutScope.options.quote_only">
                                    <div class="mb-6" v-if="checkoutScope.agree_marketing">
                                        <c-input label="Newsletter Signup" name="agree_marketing" type="switch" valuelabel="I would like to sign up to the mailing list" v-model="form.field_values.agree_marketing" :validationrule="form.validation_rules.agree_marketing" :validationmsg="form.validation_messages.agree_marketing"></c-input>
                                    </div>

                                    <template v-if="form.field_values.shipping_method != 'custom'">
                                        <div class="mb-6" v-if="checkoutScope.shipping_types.length > 1">
                                            <h3>Shipping types</h3>
                                            <div class="checkout_methods">
                                                    <div class="checkout_method" v-for="shipping_type in checkoutScope.shipping_types" :class="{ 'is_selected': form.field_values.shipping_type == shipping_type.code }" @click="checkoutScope.changeShippingType(shipping_type)">
                                                        <div class="method_content">
                                                            <header>@{{ shipping_type.name }}</header>
                                                        </div>
                                                    </div>
                                            </div>
                                        </div>
                                    </template>

                                    <template v-if="form.field_values.shipping_type != 'collection'">
                                        <div class="mb-6">
                                            <div class="mb-4">
                                                <h3>Shipping address</h3>
                                            </div>
                                            {{-- <c-user_addresses @select="checkoutScope.setShippingAddressId" :ref="checkoutScope.setShippingAddressRef" @fetch="checkoutScope.billing_address_ref ? checkoutScope.billing_address_ref.fetchAddresses() : null" :initial_selected_address_id="form.field_values.shipping_address_id">
                                                <template v-slot:default="addressScope" v-if="checkoutScope.is_logged_in && checkoutScope.use_user_addresses && !form.field_values.manual_shipping_address">
                                                    <div class="stage_wrapper--outer mb-6" :class="addressScope.stage" v-if="addressScope.is_logged_in">
                                                        <div class="stage_wrapper--inner">
                                                            <c-panel_expand :is_expanded="addressScope.stage == 'index'" class="expand__panel--no_inner expand__panel--index">
                                                                <template v-slot:default>
                                                                    <div class="user_index_address">
                                                                        <ul class="user_addresses" v-if="addressScope.addresses_fetched && addressScope.addresses.length > 0">
                                                                            <li v-for="(address, address_idx) in addressScope.addresses" :key="'shipping_' + address.id" @click.prevent.stop="addressScope.selectAddress(address.id)">
                                                                                <a href="#" @click.prevent.stop="addressScope.selectAddress(address.id)" :class="{ is_selected: address.id == addressScope.selected_address_id }">
                                                                                    <div class="mb-4">
                                                                                        <span class="name" v-if="address.name">@{{ address.name }}</span>
                                                                                        <span class="address_line_1" v-if="address.address_line_1">@{{ address.address_line_1 }}</span>
                                                                                        <span class="address_line_2" v-if="address.address_line_2">@{{ address.address_line_2 }}</span>
                                                                                        <span class="city" v-if="address.city">@{{ address.city }}</span>
                                                                                        <span class="county" v-if="address.county">@{{ address.county }}</span>
                                                                                        <span class="postcode" v-if="address.postcode">@{{ address.postcode }}</span>
                                                                                        <span class="country_name" v-if="address.country_name">@{{ address.country_name }}</span>
                                                                                    </div>
                                                                                    <div class="collection">
                                                                                        <button class="btn:sm" @click.prevent.stop="addressScope.updateAddress(address_idx)">Edit Address</button>
                                                                                        <button class="btn:sm" @click.prevent.stop="addressScope.attemptDeleteAddress(address_idx)">Delete Address</button>
                                                                                    </div>
                                                                                </a>
                                                                            </li>
                                                                        </ul>
                                                                        <span class="field__message--error" v-if="form.v$.field_values.shipping_address_id && form.v$.field_values.shipping_address_id.$error">Please select an address</span>
                                                                    </div>
                                                                    <div class="mb-6 mt-2"><a href="#" class="text-primary font-bold" @click.prevent.stop="checkoutScope.toggleManualShippingAddress">Enter a manual address</a></div>
                                                                </template>
                                                            </c-panel_expand>
                                                            <div>
                                                                <c-panel_expand :is_expanded="addressScope.stage == 'update'" class="expand__panel--no_inner expand__panel--manage">
                                                                    <template v-slot:default>
                                                                        <div class="user_update_address mb-6">
                                                                            <header>
                                                                                <span class="h5">Edit Address</span>
                                                                                <a href="#" class="address__dismiss" @click.prevent="addressScope.cancelUpdateAddress"></a>
                                                                            </header>
                                                                            <c-form :name="addressScope.forms.update_address.ref_name" :ref="addressScope.setUpdateAddressRef" :method="addressScope.forms.update_address.method" :action="addressScope.forms.update_address.action" :field_values="addressScope.forms.update_address.field_values" :field_storage="addressScope.forms.update_address.field_storage" :field_validation_rules="addressScope.forms.update_address.validation_rules" :field_validation_messages="addressScope.forms.update_address.validation_messages" @success="addressScope.successUpdateAddress">
                                                                                <template v-slot:fields="form">
                                                                                    <div class="grid:3 grid-cols-2:3 gap-x-4:3 mb-6">
                                                                                        <c-input class="col-span-2:3" name="name" v-model="form.field_values.name" :validationrule="form.validation_rules.name" :validationmsg="form.validation_messages.name" label="Address Nickname" autocomplete="off" placeholder="(eg. Home, Work)"></c-input>
                                                                                        <c-input name="address_line_1" v-model="form.field_values.address_line_1" :validationrule="form.validation_rules.address_line_1" :validationmsg="form.validation_messages.address_line_1" label="Address Line 1" autocomplete="address-line1"></c-input>
                                                                                        <c-input name="address_line_2" v-model="form.field_values.address_line_2" :validationrule="form.validation_rules.address_line_2" :validationmsg="form.validation_messages.address_line_2" label="Address Line 2" autocomplete="address-line2"></c-input>
                                                                                        <c-input name="city" v-model="form.field_values.city" :validationrule="form.validation_rules.city" :validationmsg="form.validation_messages.city" label="Town/City" autocomplete="address-level2"></c-input>
                                                                                        <c-input name="county" v-model="form.field_values.county" :validationrule="form.validation_rules.county" :validationmsg="form.validation_messages.county" label="County" autocomplete="address-level1"></c-input>
                                                                                        <c-input name="postcode" v-model="form.field_values.postcode" :validationrule="form.validation_rules.postcode" :validationmsg="form.validation_messages.postcode" label="Postcode" autocomplete="postal-code"></c-input>
                                                                                        <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" autocomplete="country-name" type="select" :options="countries"></c-input>
                                                                                    </div>

                                                                                    <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error" :autohide="true"></c-message>
                                                                                    <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success" :autohide="true"></c-message>

                                                                                    <div class="collection justify-end">
                                                                                        <a href="#" class="btn:sm border-transparent underline" @click.prevent="addressScope.cancelUpdateAddress">Cancel Edit</a>

                                                                                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Save Address</button>
                                                                                    </div>
                                                                                </template>
                                                                            </c-form>
                                                                        </div>
                                                                    </template>
                                                                </c-panel_expand>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <c-confirmation :trigger="addressScope.deletion_confirmation" @open="addressScope.openDeletionConfirmation" @close="addressScope.closeDeletionConfirmation" confirm="Yes" cancel="No" @confirm="addressScope.deleteAddress" v-if="addressScope.is_logged_in">
                                                        <h3>Confirm Deletion</h3>
                                                        <p>Are you sure you would like to delete this address?</p>
                                                    </c-confirmation>
                                                </template>
                                            </c-user_addresses> --}}
                                            <div v-if="!checkoutScope.is_logged_in || !checkoutScope.use_user_addresses || form.field_values.manual_shipping_address" class="grid:3 grid-cols-2:3 gap-x-4:3">
                                                <c-input name="shipping_address_line_1" v-model="form.field_values.shipping_address_line_1" :validationrule="form.validation_rules.shipping_address_line_1" :validationmsg="form.validation_messages.shipping_address_line_1" label="Address Line 1" autocomplete="shipping address-line1"></c-input>
                                                <c-input name="shipping_address_line_2" v-model="form.field_values.shipping_address_line_2" :validationrule="form.validation_rules.shipping_address_line_2" :validationmsg="form.validation_messages.shipping_address_line_2" label="Address Line 2" autocomplete="shipping address-line2"></c-input>
                                                <c-input name="shipping_city" v-model="form.field_values.shipping_city" :validationrule="form.validation_rules.shipping_city" :validationmsg="form.validation_messages.shipping_city" label="Town/City" autocomplete="shipping address-level2"></c-input>
                                                <c-input name="shipping_county" v-model="form.field_values.shipping_county" :validationrule="form.validation_rules.shipping_county" :validationmsg="form.validation_messages.shipping_county" label="County" autocomplete="shipping address-level1"></c-input>
                                                <c-input name="shipping_postcode" v-model="form.field_values.shipping_postcode" :validationrule="form.validation_rules.shipping_postcode" :validationmsg="form.validation_messages.shipping_postcode" label="Postcode" autocomplete="shipping postal-code"></c-input>
                                                <c-input name="shipping_country" v-model="form.field_values.shipping_country" :validationrule="form.validation_rules.shipping_country" :validationmsg="form.validation_messages.shipping_country" label="Country" autocomplete="shipping country-name" type="select" :options="checkoutScope.countries"></c-input>
                                                <template v-if="checkoutScope.is_logged_in && checkoutScope.use_user_addresses">
                                                    {{-- <div class="field__collection col-span-2:3 mb-6">
                                                        <c-input label="Save Shipping Address" name="save_shipping_address" type="checkbox" valuelabel="Save address for next time" v-model="form.field_values.save_shipping_address" :validationrule="form.validation_rules.save_shipping_address" :validationmsg="form.validation_messages.save_shipping_address"></c-input>
                                                    </div> --}}
                                                    {{-- <div class="mb-6"><a href="#" class="text-primary font-bold" @click.prevent.stop="checkoutScope.toggleManualShippingAddress">Select a saved address</a></div> --}}
                                                </template>
                                            </div>
                                        </div>
                                        <div class="mb-6 -mt-6:3" v-if="checkoutScope.billing_required">
                                            <c-input name="same_as_shipping" type="checkbox" valuelabel="My billing and shipping address are the same" v-model="form.field_values.same_as_shipping" :validationrule="form.validation_rules.same_as_shipping" :validationmsg="form.validation_messages.same_as_shipping"></c-input>
                                        </div>
                                    </template>

                                    <div class="mb-6" v-if="checkoutScope.billing_required" v-show="!form.field_values.same_as_shipping">
                                        <div class="mb-4">
                                            <h3>Billing address</h3>
                                        </div>
                                        {{-- <c-user_addresses @select="checkoutScope.setBillingAddressId" :ref="checkoutScope.setBillingAddressRef" @fetch="checkoutScope.shipping_address_ref ? checkoutScope.shipping_address_ref.fetchAddresses() : null" :initial_selected_address_id="form.field_values.billing_address_id">
                                            <template v-slot:default="addressScope" v-if="checkoutScope.is_logged_in && checkoutScope.use_user_addresses && !form.field_values.manual_billing_address">
                                                <div class="stage_wrapper--outer mb-6" :class="addressScope.stage" v-if="addressScope.is_logged_in">
                                                    <div class="stage_wrapper--inner">
                                                        <c-panel_expand :is_expanded="addressScope.stage == 'index'" class="expand__panel--no_inner expand__panel--index">
                                                            <template v-slot:default>
                                                                <div class="user_index_address">
                                                                    <ul class="user_addresses" v-if="addressScope.addresses_fetched && addressScope.addresses.length > 0">
                                                                        <li v-for="(address, address_idx) in addressScope.addresses" :key="'billing_' + address.id" @click.prevent.stop="addressScope.selectAddress(address.id)">
                                                                            <a href="#" @click.prevent.stop="addressScope.selectAddress(address.id)" :class="{ is_selected: address.id == addressScope.selected_address_id }">
                                                                                <div class="mb-4">
                                                                                    <span class="name" v-if="address.name">@{{ address.name }}</span>
                                                                                    <span class="address_line_1" v-if="address.address_line_1">@{{ address.address_line_1 }}</span>
                                                                                    <span class="address_line_2" v-if="address.address_line_2">@{{ address.address_line_2 }}</span>
                                                                                    <span class="city" v-if="address.city">@{{ address.city }}</span>
                                                                                    <span class="county" v-if="address.county">@{{ address.county }}</span>
                                                                                    <span class="postcode" v-if="address.postcode">@{{ address.postcode }}</span>
                                                                                    <span class="country_name" v-if="address.country_name">@{{ address.country_name }}</span>
                                                                                </div>
                                                                                <div class="collection">
                                                                                    <button class="btn:sm" @click.prevent.stop="addressScope.updateAddress(address_idx)">Edit Address</button>
                                                                                    <button class="btn:sm" @click.prevent.stop="addressScope.attemptDeleteAddress(address_idx)">Delete Address</button>
                                                                                </div>
                                                                            </a>
                                                                        </li>
                                                                    </ul>
                                                                    <span class="field__message--error" v-if="form.v$.field_values.billing_address_id && form.v$.field_values.billing_address_id.$error">Please select an address</span>
                                                                </div>
                                                                <div class="mb-6 mt-2 "><a href="#" class="text-primary font-bold" @click.prevent.stop="checkoutScope.toggleManualBillingAddress">Enter a manual address</a></div>
                                                            </template>
                                                        </c-panel_expand>
                                                        <div>
                                                            <c-panel_expand :is_expanded="addressScope.stage == 'update'" class="expand__panel--no_inner expand__panel--manage">
                                                                <template v-slot:default>
                                                                    <div class="user_update_address">
                                                                        <header>
                                                                            <span class="h5">Edit Address</span>
                                                                            <a href="#" class="address__dismiss" @click.prevent="addressScope.cancelUpdateAddress"></a>
                                                                        </header>
                                                                        <c-form :name="addressScope.forms.update_address.ref_name" :ref="addressScope.setUpdateAddressRef" :method="addressScope.forms.update_address.method" :action="addressScope.forms.update_address.action" :field_values="addressScope.forms.update_address.field_values" :field_storage="addressScope.forms.update_address.field_storage" :field_validation_rules="addressScope.forms.update_address.validation_rules" :field_validation_messages="addressScope.forms.update_address.validation_messages" @success="addressScope.successUpdateAddress">
                                                                            <template v-slot:fields="form">
                                                                                <div class="grid:3 grid-cols-2:3 gap-x-4:3 mb-6">
                                                                                    <c-input class="col-span-2:3" name="name" v-model="form.field_values.name" :validationrule="form.validation_rules.name" :validationmsg="form.validation_messages.name" label="Address Nickname" autocomplete="off" placeholder="(eg. Home, Work)"></c-input>
                                                                                    <c-input name="address_line_1" v-model="form.field_values.address_line_1" :validationrule="form.validation_rules.address_line_1" :validationmsg="form.validation_messages.address_line_1" label="Address Line 1" autocomplete="address-line1"></c-input>
                                                                                    <c-input name="address_line_2" v-model="form.field_values.address_line_2" :validationrule="form.validation_rules.address_line_2" :validationmsg="form.validation_messages.address_line_2" label="Address Line 2" autocomplete="address-line2"></c-input>
                                                                                    <c-input name="city" v-model="form.field_values.city" :validationrule="form.validation_rules.city" :validationmsg="form.validation_messages.city" label="Town/City" autocomplete="address-level2"></c-input>
                                                                                    <c-input name="county" v-model="form.field_values.county" :validationrule="form.validation_rules.county" :validationmsg="form.validation_messages.county" label="County" autocomplete="address-level1"></c-input>
                                                                                    <c-input name="postcode" v-model="form.field_values.postcode" :validationrule="form.validation_rules.postcode" :validationmsg="form.validation_messages.postcode" label="Postcode" autocomplete="postal-code"></c-input>
                                                                                    <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" autocomplete="country-name" type="select" :options="countries"></c-input>
                                                                                </div>

                                                                                <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error" :autohide="true"></c-message>
                                                                                <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success" :autohide="true"></c-message>

                                                                                <div class="collection justify-end">
                                                                                    <a href="#" class="btn:sm border-transparent underline" @click.prevent="addressScope.cancelUpdateAddress">Cancel Edit</a>

                                                                                    <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Save Address</button>
                                                                                </div>
                                                                            </template>
                                                                        </c-form>
                                                                    </div>
                                                                </template>
                                                            </c-panel_expand>
                                                        </div>
                                                    </div>
                                                </div>
                                                <c-confirmation :trigger="addressScope.deletion_confirmation" @open="addressScope.openDeletionConfirmation" @close="addressScope.closeDeletionConfirmation" confirm="Yes" cancel="No" @confirm="addressScope.deleteAddress" v-if="addressScope.is_logged_in">
                                                    <h3>Confirm Deletion</h3>
                                                    <p>Are you sure you would like to delete this address?</p>
                                                </c-confirmation>
                                            </template>
                                        </c-user_addresses> --}}
                                        <div v-if="!checkoutScope.is_logged_in || !checkoutScope.use_user_addresses || form.field_values.manual_billing_address" class="grid:3 grid-cols-2:3 gap-x-4:3">
                                            <c-input name="billing_address_line_1" v-model="form.field_values.billing_address_line_1" :validationrule="form.validation_rules.billing_address_line_1" :validationmsg="form.validation_messages.billing_address_line_1" label="Address Line 1" autocomplete="billing address-line1"></c-input>
                                            <c-input name="billing_address_line_2" v-model="form.field_values.billing_address_line_2" :validationrule="form.validation_rules.billing_address_line_2" :validationmsg="form.validation_messages.billing_address_line_2" label="Address Line 2" autocomplete="billing address-line2"></c-input>
                                            <c-input name="billing_city" v-model="form.field_values.billing_city" :validationrule="form.validation_rules.billing_city" :validationmsg="form.validation_messages.billing_city" label="Town/City" autocomplete="billing address-level2"></c-input>
                                            <c-input name="billing_county" v-model="form.field_values.billing_county" :validationrule="form.validation_rules.billing_county" :validationmsg="form.validation_messages.billing_county" label="County" autocomplete="billing address-level1"></c-input>
                                            <c-input class="mb-0:3" name="billing_postcode" v-model="form.field_values.billing_postcode" :validationrule="form.validation_rules.billing_postcode" :validationmsg="form.validation_messages.billing_postcode" label="Postcode" autocomplete="billing postal-code"></c-input>
                                            <c-input name="billing_country" v-model="form.field_values.billing_country" :validationrule="form.validation_rules.billing_country" :validationmsg="form.validation_messages.billing_country" label="Country" autocomplete="billing country-name" type="select" :options="checkoutScope.countries"></c-input>
                                            <template v-if="checkoutScope.is_logged_in && checkoutScope.use_user_addresses">
                                                {{-- <div class="field__collection col-span-2:3 mb-6">
                                                    <c-input label="Save Billing Address" name="save_billing_address" type="checkbox" valuelabel="Save address for next time" v-model="form.field_values.save_billing_address" :validationrule="form.validation_rules.save_billing_address" :validationmsg="form.validation_messages.save_billing_address"></c-input>
                                                </div> --}}
                                                {{-- <div class="mb-6"><a href="#" class="text-primary font-bold" @click.prevent.stop="checkoutScope.toggleManualBillingAddress">Select a saved address</a></div> --}}
                                            </template>
                                        </div>
                                    </div>

                                    <template v-if="form.field_values.shipping_method != 'custom'">
                                        <div class="mb-6" v-if="checkoutScope.available_shipping_methods.length > 0">
                                            <h3>Shipping methods</h3>
                                            <div class="checkout_methods">
                                                <div class="checkout_method" v-for="shipping_method in checkoutScope.available_shipping_methods" :class="{ 'is_selected': form.field_values.shipping_method == shipping_method.code }" @click="checkoutScope.changeShippingMethod(form, shipping_method)" :disabled="shipping_method.is_disabled">
                                                    <div class="method_content">
                                                        <header>@{{ shipping_method.name }}</header>
                                                        <span @click.stop v-if="shipping_method.description" v-html="shipping_method.description"></span>
                                                    </div>
                                                    <div class="method_price"><span>@{{ shipping_method.formatted.display_price }}</span></div>
                                                </div>
                                            </div>
                                            <span class="field__message--error" v-if="form.validation_rules.shipping_method.$invalid && form.validation_rules.shipping_method.$dirty">Please select a shipping method</span>
                                        </div>
                                    </template>

                                    <div class="mb-6">
                                        <label class="field__title">Discount Code</label>
                                        <c-message :content="checkoutScope.discount_error_message" class="message--negative" :trigger="checkoutScope.is_discount_error_message_shown" :autohide="true"></c-message>
                                        <c-message :content="checkoutScope.discount_success_message" class="message--positive" :trigger="checkoutScope.is_discount_success_message_shown" :autohide="true"></c-message>
                                        <div class="discount_wrapper">
                                            <c-input name="discount_code" label="Discount Code" v-model="form.field_values.discount_code" :validationrule="form.validation_rules.discount_code" :validationmsg="form.validation_messages.discount_code" autocomplete="off" :disabled="checkoutScope.discount > 0"></c-input>
                                            <button v-if="checkoutScope.discount > 0" @click.prevent="checkoutScope.cancelDiscountCode()" class="btn bg-secondary border-secondary text-primary_contrasting">Cancel</button>
                                            <button v-else @click.prevent="checkoutScope.applyDiscountCode()" class="btn bg-secondary border-secondary text-primary_contrasting">Apply</button>
                                        </div>
                                    </div>

                                    <div class="mb-6" v-if="!checkoutScope.hide_pricing">
                                        <h3>Order totals</h3>
                                        <div class="border-box p-5 mb-8">
                                            <table>
                                                <tbody>
                                                    <tr class="text-sm"><th>Subtotal</th><td>@{{ checkoutScope.formatted.product_subtotal }}</td></tr>
                                                    <tr class="text-sm" v-if="checkoutScope.available_shipping_methods.length > 0 && checkoutScope.shipping_count > 0 && form.validation_rules.shipping_method && !form.validation_rules.shipping_method.$invalid"><th>Shipping</th><td>@{{ checkoutScope.formatted.shipping_subtotal }}</td></tr>
                                                    <tr class="text-sm" v-if="checkoutScope.discount > 0"><th>Discount</th><td>-@{{ checkoutScope.formatted.discount }}</td></tr>
                                                    <tr class="text-sm"><th>Tax</th><td>@{{ checkoutScope.formatted.tax }}</td></tr>
                                                    <tr class="text-sm"><th>Total</th><td>@{{ checkoutScope.formatted.total }}</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    <div class="mb-6" v-if="checkoutScope.agree_terms">
                                        <c-input label="Terms & Conditions Agreement" name="agree_terms" type="switch" valuelabel="I agree to the <a target='_blank' href='{{ config('app.terms') ? config('app.terms') : url('terms') }}' class='text-primary'>Terms & Conditions</a>" v-model="form.field_values.agree_terms" :validationrule="form.validation_rules.agree_terms" :validationmsg="form.validation_messages.agree_terms"></c-input>
                                    </div>

                                    <h3>Payment details</h3>
                                    <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                                    <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                                    @if (config('account.enabled') === true)
                                        <c-payment_account
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-payment_account>
                                    @endif

                                    @if (config('cash.enabled') === true)
                                        <c-payment_cash
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-payment_cash>
                                    @endif

                                    @if (config('purchase_order.enabled') === true)
                                        <c-payment_purchase_order
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-payment_purchase_order>
                                    @endif

                                    @if (config('paypal.enabled') === true)
                                        <c-payment_paypal
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-payment_paypal>
                                    @endif

                                    @if (config('stripe.request.enabled') === true)
                                        <c-payment_stripe_request
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :customer_id="form.field_values.stripe_customer_id"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-payment_stripe_request>
                                    @endif

                                    @if (config('stripe.enabled') === true)
                                        <c-payment_stripe
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :customer_id="form.field_values.stripe_customer_id"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                            :billing_address_city="form.field_values.billing_city"
                                            :billing_address_country="form.field_values.billing_country"
                                            :billing_address_line1="form.field_values.billing_address_line_1"
                                            :billing_address_line2="form.field_values.billing_address_line_2"
                                            :billing_address_postal_code="form.field_values.billing_postcode"
                                            :billing_address_state="form.field_values.billing_county"
                                            :billing_email="form.field_values.email"
                                            :billing_name="checkoutScope.full_name"
                                            :billing_phone="form.field_values.phone"
                                            :can_save_cards="true"
                                        ></c-payment_stripe>
                                    @endif
                                </template>
                                <template v-else>
                                    <c-input class="-mt-2" name="notes" v-model="form.field_values.notes" :validationrule="form.validation_rules.notes" :validationmsg="form.validation_messages.notes" label="Notes" type="textarea"></c-input>

                                    <c-input label="Quote Can Be Edited" name="can_edit" type="switch" valuelabel="The quote items can be edited by the customer" v-model="form.field_values.can_edit" :validationrule="form.validation_rules.can_edit" :validationmsg="form.validation_messages.can_edit"></c-input>

                                    <c-input label="Quote Can Be Edited Whilst Keeping Minimum Quantities" name="enforce_minimum_quantities" type="switch" valuelabel="The edited quote items must meet or exceed the original quoted quantities" v-model="form.field_values.enforce_minimum_quantities" :validationrule="form.validation_rules.enforce_minimum_quantities" :validationmsg="form.validation_messages.enforce_minimum_quantities"></c-input>

                                    <c-input label="Low Resolution Artwork Provided" name="low_res_artwork_provided" type="switch" valuelabel="Temporary low resolution artwork has been used" v-model="form.field_values.low_res_artwork_provided" :validationrule="form.validation_rules.low_res_artwork_provided" :validationmsg="form.validation_messages.low_res_artwork_provided"></c-input>

                                    <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                                    <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                                    @if (config('onecrm.enabled') === true)
                                        <c-checkout_quote
                                            @validate="form.validate"
                                            @load="form.load"
                                            @unload="form.unload"
                                            @success="form.successResponse"
                                            @fail="form.failResponse"

                                            :is_valid="!form.v$.$invalid"
                                            :amount="checkoutScope.total"
                                            :additional_data="form.field_values"
                                            redirect_url="{{ url(config('shop.redirect_url')) }}"
                                        ></c-checkout_quote>
                                    @endif
                                </template>
                            </template>
                        </c-form>
                    </template>

                    <template v-if="checkoutScope.product_items.length == 0 && checkoutScope.cart_fetched">
                        <h1 class="text-gradient">Shopping cart</h1>
                        <p>You have no items in your shopping cart.</p>
                        <p>
                            <a href="{{ route('shop.product.index') }}" class="btn bg-primary border-primary text-primary_contrasting">Back to Shop</a>
                        </p>
                    </template>
                </template>
            </c-checkout>
        </div>
    </div>
@endsection

@section('config')
'payments.stripe.stripe_customer_id': '{!! $stripe_customer_id !!}',
'shop.first_name': '{!! $first_name !!}',
'shop.last_name': '{!! $last_name !!}',
'shop.email': '{!! $email !!}',
@endsection