@extends('layouts.default')
@php
    $default_title = 'Register';
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
        <div class="container:sm">
            <div class="bg-background border-box p-8 p-10:3">
                <h1 class="text-gradient">Register</h1>
                <p>Already have an account? <a href="{{ route('login') }}">Login</a></p>
                <c-form :name="forms.register.ref" :ref="forms.register.ref" method="post" :action="forms.register.action" :field_values="forms.register.field_values" :field_storage="forms.register.field_storage" :field_validation_rules="forms.register.validation_rules" :field_validation_messages="forms.register.validation_messages">
                    <template v-slot:fields="form">
                        <c-input name="first_name" v-model="form.field_values.first_name" :validationrule="form.validation_rules.first_name" :validationmsg="form.validation_messages.first_name" label="First Name" autocomplete="given-name"></c-input>
                        <c-input name="last_name" v-model="form.field_values.last_name" :validationrule="form.validation_rules.last_name" :validationmsg="form.validation_messages.last_name" label="Last Name" autocomplete="family-name"></c-input>
                        @if (!empty(config('shop.minimum_country_qty')) === true)
                            <c-input name="shipping_country" v-model="form.field_values.shipping_country" :validationrule="form.validation_rules.shipping_country" :validationmsg="form.validation_messages.shipping_country" label="Shipping Country" autocomplete="shipping country-name" type="select" :options="countries"></c-input>
                        @endif
                        <c-input name="email" type="email" label="Email address" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" autocomplete="email"></c-input>
                        <c-input name="password" type="password" label="Password" v-model="form.field_values.password" :validationrule="form.validation_rules.password" :validationmsg="form.validation_messages.password" autocomplete="new-password"></c-input>
                        <c-input name="password_confirmation" type="password" label="Confirmation Password" v-model="form.field_values.password_confirmation" :validationrule="form.validation_rules.password_confirmation" :validationmsg="form.validation_messages.password_confirmation" autocomplete="new-password"></c-input>

                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Register</button>
                    </template>
                </c-form>
            </div>
        </div>
    </div>
@endsection