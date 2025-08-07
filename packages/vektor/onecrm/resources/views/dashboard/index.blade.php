@extends('layouts.dashboard')
@php
    $default_title = 'My Account';
@endphp
@if (isset($page))
    @section('title', $page->title ? $page->title : $default_title)
    @section('meta_title', !empty($page->meta_title) ? $page->meta_title : $page->title)
    @section('meta_description', $page->meta_description)
    @section('meta_image', $page->meta_image)
@else
    @section('title', $default_title)
@endif

@section('content.dashboard')
<c-onecrm_dashboard>
    <template v-slot:default="dashboard">
        <div class="border-box bg-background p-8">
            <header class="mb-6 flex items-center">
                <h1 class="text-gradient mb-0">My account</h1>
                <div class="actions ml-auto" v-if="dashboard.panels.personal_details.data_fetched">
                    <a class="btn:sm bg-primary border-primary text-primary_contrasting" @click="dashboard.panels.personal_details.is_expanded = !dashboard.panels.personal_details.is_expanded">Edit details</a>
                </div>
            </header>
            <c-panel_expand v-if="dashboard.panels.personal_details.data_fetched" :is_expanded="dashboard.panels.personal_details.is_expanded">
                <c-form
                        :name="dashboard.forms.personal_details.ref"
                        :ref="dashboard.forms.personal_details.ref"
                        method="post"
                        :action="dashboard.forms.personal_details.action"
                        :field_values="dashboard.forms.personal_details.field_values"
                        :field_storage="dashboard.forms.personal_details.field_storage" :field_validation_rules="dashboard.personal_details_validation_rules" :field_validation_messages="dashboard.forms.personal_details.validation_messages"
                        :clear_fields="true"
                        @success="dashboard.personalDetailsSuccess"
                    >
                    <template v-slot:fields="form">
                        <div class="mb-6">
                            <div class="mb-4">
                                <h3>Personal details</h3>
                            </div>
                            <div class="grid:3 grid-cols-2:3 gap-x-4:3">
                                <c-input name="first_name" label="First Name" v-model="form.field_values.first_name" :validationrule="form.validation_rules.first_name" :validationmsg="form.validation_messages.first_name" autocomplete="given-name"></c-input>
                                <c-input name="last_name" label="Last Name" v-model="form.field_values.last_name" :validationrule="form.validation_rules.last_name" :validationmsg="form.validation_messages.last_name" autocomplete="family-name"></c-input>
                                <c-input name="phone" type="tel" label="Phone Number" v-model="form.field_values.phone" :validationrule="form.validation_rules.phone" :validationmsg="form.validation_messages.phone" autocomplete="tel"></c-input>
                                <c-input name="email" type="email" label="Email address" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" autocomplete="email"></c-input>
                                <c-input name="new_password" type="password" label="New Password" placeholder="Enter a password" v-model="form.field_values.new_password" :validationrule="form.validation_rules.new_password" :validationmsg="form.validation_messages.new_password" autocomplete="new-password"></c-input>
                                <c-input name="new_password_confirmation" type="password" label="New Confirmation Password" placeholder="Confirm your password" v-model="form.field_values.new_password_confirmation" :validationrule="form.validation_rules.new_password_confirmation" :validationmsg="form.validation_messages.new_password_confirmation" autocomplete="new-password"></c-input>
                                <c-input name="password" type="password" label="Existing Password" placeholder="Enter a password" v-model="form.field_values.password" :validationrule="form.validation_rules.password" :validationmsg="form.validation_messages.password" autocomplete="current-password"></c-input>
                            </div>
                        </div>
                        <div class="mb-6">
                            <div class="mb-4">
                                <h3>Shipping address</h3>
                            </div>
                            <div class="grid:3 grid-cols-2:3 gap-x-4:3">
                                <c-input name="shipping_address_line_1" v-model="form.field_values.shipping_address_line_1" :validationrule="form.validation_rules.shipping_address_line_1" :validationmsg="form.validation_messages.shipping_address_line_1" label="Address Line 1" autocomplete="shipping address-line1"></c-input>
                                <c-input name="shipping_address_line_2" v-model="form.field_values.shipping_address_line_2" :validationrule="form.validation_rules.shipping_address_line_2" :validationmsg="form.validation_messages.shipping_address_line_2" label="Address Line 2" autocomplete="shipping address-line2"></c-input>
                                <c-input name="shipping_city" v-model="form.field_values.shipping_city" :validationrule="form.validation_rules.shipping_city" :validationmsg="form.validation_messages.shipping_city" label="Town/City" autocomplete="shipping address-level2"></c-input>
                                <c-input name="shipping_county" v-model="form.field_values.shipping_county" :validationrule="form.validation_rules.shipping_county" :validationmsg="form.validation_messages.shipping_county" label="County" autocomplete="shipping address-level1"></c-input>
                                <c-input name="shipping_postcode" v-model="form.field_values.shipping_postcode" :validationrule="form.validation_rules.shipping_postcode" :validationmsg="form.validation_messages.shipping_postcode" label="Postcode" autocomplete="shipping postal-code"></c-input>
                                <c-input name="shipping_country" v-model="form.field_values.shipping_country" :validationrule="form.validation_rules.shipping_country" :validationmsg="form.validation_messages.shipping_country" label="Country" autocomplete="shipping country-name" type="select" :options="dashboard.countries"></c-input>
                            </div>
                        </div>
                        <div class="mb-6">
                            <c-input name="same_as_shipping" type="checkbox" valuelabel="My billing and shipping address are the same" v-model="form.field_values.same_as_shipping" :validationrule="form.validation_rules.same_as_shipping" :validationmsg="form.validation_messages.same_as_shipping"></c-input>
                        </div>
                        <div class="mb-6" v-show="!form.field_values.same_as_shipping">
                            <div class="mb-4">
                                <h3>Billing address</h3>
                            </div>
                            <div class="grid:3 grid-cols-2:3 gap-x-4:3">
                                <c-input name="billing_address_line_1" v-model="form.field_values.billing_address_line_1" :validationrule="form.validation_rules.billing_address_line_1" :validationmsg="form.validation_messages.billing_address_line_1" label="Address Line 1" autocomplete="billing address-line1"></c-input>
                                <c-input name="billing_address_line_2" v-model="form.field_values.billing_address_line_2" :validationrule="form.validation_rules.billing_address_line_2" :validationmsg="form.validation_messages.billing_address_line_2" label="Address Line 2" autocomplete="billing address-line2"></c-input>
                                <c-input name="billing_city" v-model="form.field_values.billing_city" :validationrule="form.validation_rules.billing_city" :validationmsg="form.validation_messages.billing_city" label="Town/City" autocomplete="billing address-level2"></c-input>
                                <c-input name="billing_county" v-model="form.field_values.billing_county" :validationrule="form.validation_rules.billing_county" :validationmsg="form.validation_messages.billing_county" label="County" autocomplete="billing address-level1"></c-input>
                                <c-input name="billing_postcode" v-model="form.field_values.billing_postcode" :validationrule="form.validation_rules.billing_postcode" :validationmsg="form.validation_messages.billing_postcode" label="Postcode" autocomplete="billing postal-code"></c-input>
                                <c-input name="billing_country" v-model="form.field_values.billing_country" :validationrule="form.validation_rules.billing_country" :validationmsg="form.validation_messages.billing_country" label="Country" autocomplete="billing country-name" type="select" :options="dashboard.countries"></c-input>
                            </div>
                        </div>

                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Save my details</button>
                    </template>
                </c-form>
            </c-panel_expand>
            <hr style="margin-top: -1px;">
            <div class="relative" style="min-height: 200px;">
                <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: dashboard.panels.personal_details.is_loading == true }">
                    <div class="spinner"></div>
                </div>
                <div class="mt-4">
                    <p v-if="dashboard.panels.personal_details.full_name"><strong>Name:</strong> @{{ dashboard.panels.personal_details.full_name }}</p>
                    <div class="grid grid-cols-2 gap-x-4">
                        <div v-if="dashboard.panels.personal_details.shipping_address">
                            <h3>Shipping address</h3>
                            <p v-html="dashboard.panels.personal_details.shipping_address"></p>
                        </div>
                        <div v-if="dashboard.panels.personal_details.billing_address">
                            <h3>Billing address</h3>
                            <p v-html="dashboard.panels.personal_details.billing_address"></p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="collection metadata">
                            <div v-if="dashboard.panels.personal_details.phone">
                                <p><strong>Phone:</strong> <a :href="'tel:' + dashboard.panels.personal_details.tel">@{{ dashboard.panels.personal_details.phone }}</a></p>
                            </div>
                            <div v-if="dashboard.panels.personal_details.email">
                                <p><strong>Email:</strong> <a :href="'mailto:' + dashboard.panels.personal_details.email">@{{ dashboard.panels.personal_details.email }}</a></p>
                            </div>
                        </div>
                    </div>
                </div>
                @if (config('stripe.enabled'))
                    <div class="mt-12" v-if="dashboard.panels.personal_details.data_fetched">
                        <h3>Saved cards</h3>
                        <c-payment_stripe_cards
                            :customer_id="dashboard.panels.personal_details.stripe_customer_id"
                            :billing_address_city="dashboard.panels.personal_details.billing_city"
                            :billing_address_country="dashboard.panels.personal_details.billing_country"
                            :billing_address_line1="dashboard.panels.personal_details.billing_address_line_1"
                            :billing_address_line2="dashboard.panels.personal_details.billing_address_line_2"
                            :billing_address_postal_code="dashboard.panels.personal_details.billing_postcode"
                            :billing_address_state="dashboard.panels.personal_details.billing_county"
                            :billing_email="dashboard.panels.personal_details.email"
                            :billing_name="dashboard.panels.personal_details.full_name"
                            :billing_phone="dashboard.panels.personal_details.phone"
                            :can_save_cards="true"
                            @success="dashboard.stripeCardsSuccess"
                        ></c-payment_stripe_cards>
                    </div>
                @endif

                <c-saved_carts
                @update:cart="updateCartCount"
                @message:hide="hideMessage"
                @message:success="successMessage"
                @message:error="errorMessage" v-if="dashboard.panels.personal_details.data_fetched">
                    <template v-slot:default="cartScope">
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
                </c-saved_carts>
            </div>
        </div>
    </template>
</c-onecrm_dashboard>
@endsection