@extends('layouts.default')
@php
    $default_title = 'Create New Password';
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
                <h1 class="text-gradient">Create new password</h1>
                <p>Your new password must be different from previously used passwords.</p>
                <c-form :name="forms.password_update.ref" :ref="forms.password_update.ref" method="post" :action="forms.password_update.action" :field_values="forms.password_update.field_values" :field_storage="forms.password_update.field_storage" :field_validation_rules="forms.password_update.validation_rules" :field_validation_messages="forms.password_update.validation_messages">
                    <template v-slot:fields="form">
                        <c-input name="email" type="email" label="Email address" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" autocomplete="email"></c-input>
                        <c-input name="password" type="password" label="Password" v-model="form.field_values.password" :validationrule="form.validation_rules.password" :validationmsg="form.validation_messages.password" autocomplete="new-password"></c-input>
                        <c-input name="password_confirmation" type="password" label="Confirmation Password" v-model="form.field_values.password_confirmation" :validationrule="form.validation_rules.password_confirmation" :validationmsg="form.validation_messages.password_confirmation" autocomplete="new-password"></c-input>
                        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
                        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

                        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Reset password</button>
                    </template>
                </c-form>
            </div>
        </div>
    </div>
@endsection

@section('config')
'forms.password_update.token': '{{ $token }}',
'forms.password_update.email': '{{ $email ?? old('email') }}',
@endsection