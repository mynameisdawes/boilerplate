@extends('layouts.default')
@php
    $default_title = 'Reset Password';
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
                <h1 class="text-gradient">Reset password</h1>
                <p>Enter the email address associated with your account and we'll send an email with instructions to reset your password.</p>
                <c-form :name="forms.password_email.ref" :ref="forms.password_email.ref" method="post" :action="forms.password_email.action" :field_values="forms.password_email.field_values" :field_storage="forms.password_email.field_storage" :field_validation_rules="forms.password_email.validation_rules" :field_validation_messages="forms.password_email.validation_messages">
                    <template v-slot:fields="form">
                        <c-input name="email" type="email" label="Email address" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" autocomplete="email"></c-input>
                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Send instructions</button>
                    </template>
                </c-form>
            </div>
        </div>
    </div>
@endsection