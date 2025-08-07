@extends('layouts.default')
{{-- @section('title', 'Homepage') --}}

@section('content')
    <div class="hero hero-home bg-image" style="background-image: image-set('/assets/img/background__01.webp' type('image/webp'), '/assets/img/background__01.jpg' type('image/jpeg'));">
        <div class="container:xl">
            <h1>Smoke without Fire</h1>
        </div>
    </div>
    <div class="container-gutter">
        <div class="container:xl">
            <div class="layout_bento_primary">
                <div class="rounded-box p-6 p-8:2 bg-secondary text-secondary_contrasting bg-image" style="background-image: image-set('/assets/img/background__01.webp' type('image/webp'), '/assets/img/background__01.jpg' type('image/jpeg'));">
                    <div><p>
                        <span>Up to</span>
                        <span>13x</span>
                        <span>faster performance</span>
                    </p></div>
                </div>
                <div class="rounded-box p-6 p-8:2 bg-secondary text-secondary_contrasting bg-image" style="background-image: linear-gradient(to top, rgba(0,0,0,0.9) 16%, rgba(0,0,0,0)), url('https://images.unsplash.com/photo-1492288991661-058aa541ff43?q=80&w=1200&auto=format&fit=crop')">
                    <div>
                        <p class="h2">Sustainable</p>
                        <p>Recycled materials, responsible packaging - This product is designed with <u>you</u> in mind</p>
                    </div>
                </div>
                <div class="rounded-box p-6 p-8:2 bg-primary text-primary_contrasting bg-image" style="background-image: linear-gradient(to top, rgba(0,0,0,0.4) 16%, rgba(0,0,0,0)), url('https://images.unsplash.com/photo-1604881991405-b273c7a4386a?q=80&w=1200&auto=format&fit=crop')">
                    <div><p class="h2">24/7 Support Team</p></div>
                </div>
                <div class="rounded-box p-6 p-8:2 bg-secondary text-secondary_contrasting bg-image" style="background-image: linear-gradient(to top, oklch(0.4146 0.1866 13.23) 16%, oklch(0.28 0.13 359.77))">
                    <div>
                        <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="90px" height="74.1px" viewBox="0 0 90 74.1" style="enable-background:new 0 0 90 74.1;" xml:space="preserve"><path class="fill-current" d="M90,55.9V0H0v55.9h38.9v12.8h-10v5.4h32.4v-5.4h-10V55.9H90z M3.8,52.2V3.8h82.5v48.4H3.8z M30.5,17.6 c-6,0-10.9,4.9-10.9,10.9c0,6,4.9,10.9,10.9,10.9c6,0,10.9-4.9,10.9-10.9C41.5,22.5,36.6,17.6,30.5,17.6z M30.5,37.6 c-5,0-9.1-4.1-9.1-9.1s4.1-9.1,9.1-9.1s9.1,4.1,9.1,9.1S35.5,37.6,30.5,37.6z M47.2,28.4h16.9v-1.9H47.2V28.4z M47.2,22.9h22.2V21 H47.2V22.9z M47.2,34h13.3v-1.9H47.2V34z"/></svg>
                        <p>Up to 6 more hours of battery life<br /><small>(up to 18 hours total)<sup>1</sup></small></p>
                    </div>
                </div>
                <div class="rounded-box p-6 p-8:2 bg-secondary text-secondary_contrasting bg-image" style="background-image: linear-gradient(to top, oklch(0.4146 0.1866 13.23) 16%, rgba(0,0,0,0)), image-set('/assets/img/background__01.webp' type('image/webp'), '/assets/img/background__01.jpg' type('image/jpeg'));">
                    <div><p>We help you to grow through the use of custom merchandise, design and digital</p></div>
                </div>
            </div>
        </div>
        <div>
            <c-user_addresses>
                <template v-slot:default="addressScope">
                    <div class="stage_wrapper--outer" :class="addressScope.stage" v-if="addressScope.is_logged_in">
                        <div class="stage_wrapper--inner">
                            <c-panel_expand :is_expanded="addressScope.stage == 'index'" class="expand__panel--no_inner expand__panel--index">
                                <template v-slot:default>
                                    <div class="container:xl">
                                        <div class="user_index_address">
                                            <header>
                                                <span class="h3">Manage Addresses</span>
                                            </header>
                                            <ul class="user_addresses" v-if="addressScope.addresses_fetched && addressScope.addresses.length > 0">
                                                <li v-for="(address, address_idx) in addressScope.addresses" :key="address.id" @click.prevent.stop="addressScope.selectAddress(address.id)">
                                                    <a href="#" @click.prevent.stop="addressScope.selectAddress(address.id)" :class="{ is_selected: address.id == addressScope.selected_address_id }">
                                                        <div class="badges" v-if="address.is_default_shipping || address.is_default_billing">
                                                            <div class="badge" v-if="address.is_default_shipping">Shipping</div>
                                                            <div class="badge" v-if="address.is_default_billing">Billing</div>
                                                        </div>
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
                                            <button class="btn:sm mt-4" @click.prevent.stop="addressScope.createAddress">Add New Address</button>
                                        </div>
                                    </div>
                                </template>
                            </c-panel_expand>
                            <div>
                                <c-panel_expand :is_expanded="addressScope.stage == 'create'" class="expand__panel--no_inner expand__panel--manage">
                                    <template v-slot:default>
                                        <div class="container:xl">
                                            <div class="user_create_address">
                                                <header>
                                                    <span class="h3">Add New Address</span>
                                                    <a href="#" class="address__dismiss" @click.prevent="addressScope.cancelCreateAddress"></a>
                                                </header>
                                                <c-form :name="addressScope.forms.create_address.ref_name" :ref="addressScope.setCreateAddressRef" :method="addressScope.forms.create_address.method" :action="addressScope.forms.create_address.action" :field_values="addressScope.forms.create_address.field_values" :field_storage="addressScope.forms.create_address.field_storage" :field_validation_rules="addressScope.forms.create_address.validation_rules" :field_validation_messages="addressScope.forms.create_address.validation_messages" @success="addressScope.successCreateAddress">
                                                    <template v-slot:fields="form">
                                                        <div class="grid:3 grid-cols-2:3 gap-x-4:3 mb-6">
                                                            <c-input class="col-span-2:3" name="name" v-model="form.field_values.name" :validationrule="form.validation_rules.name" :validationmsg="form.validation_messages.name" label="Address Nickname" autocomplete="off" placeholder="(eg. Home, Work)"></c-input>
                                                            <c-input name="address_line_1" v-model="form.field_values.address_line_1" :validationrule="form.validation_rules.address_line_1" :validationmsg="form.validation_messages.address_line_1" label="Address Line 1" autocomplete="address-line1"></c-input>
                                                            <c-input name="address_line_2" v-model="form.field_values.address_line_2" :validationrule="form.validation_rules.address_line_2" :validationmsg="form.validation_messages.address_line_2" label="Address Line 2" autocomplete="address-line2"></c-input>
                                                            <c-input name="city" v-model="form.field_values.city" :validationrule="form.validation_rules.city" :validationmsg="form.validation_messages.city" label="Town/City" autocomplete="address-level2"></c-input>
                                                            <c-input name="county" v-model="form.field_values.county" :validationrule="form.validation_rules.county" :validationmsg="form.validation_messages.county" label="County" autocomplete="address-level1"></c-input>
                                                            <c-input name="postcode" v-model="form.field_values.postcode" :validationrule="form.validation_rules.postcode" :validationmsg="form.validation_messages.postcode" label="Postcode" autocomplete="postal-code"></c-input>
                                                            <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" :suggestions="countries" :suggestions_model="true" type="autocomplete" autocomplete="country-name"></c-input>
                                                            {{-- <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" autocomplete="shipping country-name" type="select" :options="countries"></c-input> --}}

                                                            <div class="field__collection col-span-2:3">
                                                                <c-input label="Default Shipping Address" name="is_default_shipping" type="checkbox" valuelabel="Use as default shipping address" v-model="form.field_values.is_default_shipping" :validationrule="form.validation_rules.is_default_shipping" :validationmsg="form.validation_messages.is_default_shipping"></c-input>
                                                                <c-input label="Default Billing Address" name="is_default_billing" type="checkbox" valuelabel="Use as default billing address" v-model="form.field_values.is_default_billing" :validationrule="form.validation_rules.is_default_billing" :validationmsg="form.validation_messages.is_default_billing"></c-input>
                                                            </div>
                                                        </div>

                                                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error" :autohide="true"></c-message>
                                                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success" :autohide="true"></c-message>

                                                        <div class="collection justify-end">
                                                            <a href="#" class="btn:sm border-transparent underline" @click.prevent="addressScope.cancelCreateAddress">Cancel Add</a>

                                                            <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Add Address</button>
                                                        </div>
                                                    </template>
                                                </c-form>
                                            </div>
                                        </div>
                                    </template>
                                </c-panel_expand>
                                <c-panel_expand :is_expanded="addressScope.stage == 'update'" class="expand__panel--no_inner expand__panel--manage">
                                    <template v-slot:default>
                                        <div class="container:xl">
                                            <div class="user_update_address">
                                                <header>
                                                    <span class="h3">Edit Address</span>
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
                                                            <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" :suggestions="countries" :suggestions_model="true" type="autocomplete" autocomplete="country-name"></c-input>
                                                            {{-- <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" autocomplete="shipping country-name" type="select" :options="countries"></c-input> --}}
                                                            <div class="field__collection col-span-2:3">
                                                                <c-input label="Default Shipping Address" name="is_default_shipping" type="checkbox" valuelabel="Use as default shipping address" v-model="form.field_values.is_default_shipping" :validationrule="form.validation_rules.is_default_shipping" :validationmsg="form.validation_messages.is_default_shipping"></c-input>
                                                                <c-input label="Default Billing Address" name="is_default_billing" type="checkbox" valuelabel="Use as default billing address" v-model="form.field_values.is_default_billing" :validationrule="form.validation_rules.is_default_billing" :validationmsg="form.validation_messages.is_default_billing"></c-input>
                                                            </div>
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
            </c-user_addresses>
        </div>
        <div class="container:xl">
            <div class="container-gutter:inner grid-cols-2:3">
                <div>
                    <h2>An introduction</h2>
                    <p>At our agency, we believe that every business deserves the opportunity to thrive and succeed in today's competitive market. Our team of experienced professionals is dedicated to providing innovative and effective solutions to help our clients achieve their goals.</p><p>We understand that each business is unique, and that's why we take a personalized approach to every project we work on. Whether you're looking to increase your online presence, launch a new product, or improve your customer engagement, we have the skills and expertise to make it happen. With our help, you can take your business to the next level and achieve the success you deserve.</p>
                    <div class="collection">
                        <button @click.prevent="modal_trigger = true" class="btn bg-primary border-primary text-primary_contrasting">Open Modal</button>
                    </div>
                </div>
                <div class="order-first:3">
                    <h2>Who are we?</h2>
                    <p>We pride ourselves on our ability to deliver results. Our team of experts is highly skilled in a range of disciplines, including marketing, branding, web design, and development. We combine our knowledge and expertise to create powerful, integrated solutions that help our clients achieve their business objectives.</p><p>Our approach is data-driven and results-focused, ensuring that every strategy we develop is based on real insights and metrics. We are committed to delivering exceptional value to our clients and building long-term partnerships that help them achieve sustained growth and success. Contact us today to learn more about how we can help you take your business to the next level.</p>
                    <button @click.prevent="confirmation_trigger = true" class="btn">Open Confirmation</button>
                </div>
            </div>
        </div>
    </div>
    <div class="py-6 py-20:3 bg-image" style="background-image: image-set('/assets/img/background__01.webp' type('image/webp'), '/assets/img/background__01.jpg' type('image/jpeg')); background-position: center top;">
        <div class="container:md">
            <div class="cta_box">
                <h2>A call to action</h2>
                <p>Ready to take your online presence to the next level? Contact us today to schedule a consultation and learn how our expert team can help you achieve your web development and marketing goals. With our customized solutions and data-driven approach, we'll help you build a website that not only looks great but also delivers results. Don't wait, contact us now to get started!</p>
            </div>
        </div>
    </div>
    <div class="container-gutter">
        <div class="container:xl">
            <div mode="right" class="sidebar__wrapper sidebar__wrapper--sticky">
                <c-contents :items="[ { name: 'area_1', label: 'Area 1'}, { name: 'area_2', label: 'Area 2'} ]"></c-contents>
                <section>
                    <h2>Heading</h2>
                    <div class="grid grid-cols-2:4 gap-6 mb-4 mb-8:2">
                        <div class="border-box p-8 bg-background">
                            <h3 class="h4">Card heading</h3>
                            <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, <a href="#" @click.prevent="">because it is pleasure</a>, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p>
                            <a href="#" class="btn bg-secondary border-secondary text-secondary_contrasting">Click here</a>
                        </div>
                        <div class="border-box p-8 border-transparent bg-secondary text-secondary_contrasting">
                            <h3 class="h4">Card heading</h3>
                            <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, <a href="#" @click.prevent="">because it is pleasure</a>, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p>
                            <a href="#" class="btn bg-white border-white text-black">Click here</a>
                        </div>
                    </div>
                    <article>
                        <h3 class="h4" id="area_1">Section heading</h3>
                        <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, <a href="#" @click.prevent="">because it is pleasure</a>, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p>
                        <h3 class="h4" id="area_2">Section heading</h3>
                        <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, <a href="#" @click.prevent="">because it is pleasure</a>, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful. Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p>
                    </article>
                </section>
            </div>
        </div>
        <div class="container:xl">
            <table class="table--responsive:1t2e">
                <thead>
                    <tr>
                        <th>Header</th>
                        <th>Header</th>
                        <th>Header</th>
                        <th>Header</th>
                        <th>Header</th>
                        <th>Header</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                    <tr>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                        <td data-header="Header">Table Content</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <c-modal :trigger="modal_trigger" @open="modal_trigger = true" @close="modal_trigger = false">
        <h3>Modal heading</h3>
        <p>But I must explain to you how all this mistaken idea of denouncing pleasure and praising pain was born and I will give you a complete account of the system, and expound the actual teachings of the great explorer of the truth, the master-builder of human happiness. No one rejects, dislikes, or avoids pleasure itself, because it is pleasure, but because those who do not know how to pursue pleasure rationally encounter consequences that are extremely painful.</p>
        <p>Nor again is there anyone who loves or pursues or desires to obtain pain of itself, because it is pain, but because occasionally circumstances occur in which toil and pain can procure him some great pleasure.</p>
    </c-modal>
    <c-confirmation :trigger="confirmation_trigger" @open="confirmation_trigger = true" @close="confirmation_trigger = false" confirm="Yes" cancel="No">
        <h3>Crucial choices</h3>
        <p>Are yo sure?!?!?</p>
    </c-confirmation>
@endsection
