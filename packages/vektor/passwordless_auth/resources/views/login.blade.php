@extends('layouts.default')
@php
    $default_title = 'Login';
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
    <div class="pt-4 pb-12 pt-8:2 pb-16:2 pt-12:3 pb-12:3">
        <div class="container:sm">
            <div class="bg-background border-box p-8 p-10:3 relative mb-2">
                <h1 class="text-gradient">Login</h1>
                <p>Don't have an account? <a href="{{ route('passwordless.register') }}">Register</a></p>
                <c-form :name="forms.passwordless_login.ref" :ref="forms.passwordless_login.ref" method="post" :action="forms.passwordless_login.action" :field_values="forms.passwordless_login.field_values" :field_storage="forms.passwordless_login.field_storage" :field_validation_rules="forms.passwordless_login.validation_rules" :field_validation_messages="forms.passwordless_login.validation_messages">
                    <template v-slot:fields="form">
                        <c-input name="email" type="email" label="Email address" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" autocomplete="email"></c-input>
                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Send one-time link</button>
                    </template>
                </c-form>
            </div>
        </div>
    </div>
@endsection