<c-form :name="forms.contact.ref" :ref="forms.contact.ref" method="post" :action="forms.contact.action" :field_values="forms.contact.field_values" :field_storage="forms.contact.field_storage" :field_validation_rules="forms.contact.validation_rules" :field_validation_messages="forms.contact.validation_messages" :clear_fields="true">
    <template v-slot:fields="form">
        <div class="mb-4">
            <h3>Personal details</h3>
        </div>
        <div class="grid:3 grid-cols-2:3 gap-x-4:3 mb-6">
            <c-input name="title" v-model="form.field_values.title" :validationrule="form.validation_rules.title" :validationmsg="form.validation_messages.title" label="Title" autocomplete="honorific-prefix" type="select" :options="[ { value: 'Mr', text: 'Mr' }, { value: 'Mrs', text: 'Mrs' }, { value: 'Miss', text: 'Miss' } ]"></c-input>
            <c-input name="first_name" v-model="form.field_values.first_name" :validationrule="form.validation_rules.first_name" :validationmsg="form.validation_messages.first_name" label="First Name" autocomplete="given-name"></c-input>
            <c-input name="last_name" v-model="form.field_values.last_name" :validationrule="form.validation_rules.last_name" :validationmsg="form.validation_messages.last_name" label="Last Name" autocomplete="family-name"></c-input>
            <c-input name="email" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" label="Email" autocomplete="email" type="email"></c-input>
            <c-input name="phone" type="tel" v-model="form.field_values.phone" :validationrule="form.validation_rules.phone" :validationmsg="form.validation_messages.phone" label="Phone Number" autocomplete="tel"></c-input>
        </div>
        <div class="grid grid-cols-2:3 gap-4 mb-6">
            <div class="field__collection">
                <c-input label="Newsletter Signup" name="agree_marketing" type="checkbox" valuelabel="Sign up to our newsletter?" v-model="form.field_values.agree_marketing" :validationrule="form.validation_rules.agree_marketing" :validationmsg="form.validation_messages.agree_marketing"></c-input>
                <c-input label="Light Switcher" name="light" type="switch" valuelabel="Switch on the lights?" v-model="form.field_values.light" :validationrule="form.validation_rules.light" :validationmsg="form.validation_messages.light"></c-input>
            </div>
            <div class="field__collection">
                <span class="field__collection__title">Would you like us to call you back?</span>
                <c-input label="Callback" name="callback" type="radio" value="yes" valuelabel="Yes" v-model="form.field_values.callback" :validationrule="form.validation_rules.callback" :validationmsg="form.validation_messages.callback"></c-input>
                <c-input label="Callback" name="callback" type="radio" value="no" valuelabel="No" v-model="form.field_values.callback" :validationrule="form.validation_rules.callback" :validationmsg="form.validation_messages.callback"></c-input>
            </div>
        </div>
        <div class="mb-4">
            <h3>Further details</h3>
        </div>
        <div class="grid:3 grid-cols-2:3 gap-x-4:3 mb-6">
            <c-input name="address_line_1" v-model="form.field_values.address_line_1" :validationrule="form.validation_rules.address_line_1" :validationmsg="form.validation_messages.address_line_1" label="Address Line 1" autocomplete="address-line1"></c-input>
            <c-input name="address_line_2" v-model="form.field_values.address_line_2" :validationrule="form.validation_rules.address_line_2" :validationmsg="form.validation_messages.address_line_2" label="Address Line 2" autocomplete="address-line2"></c-input>
            <c-input name="city" v-model="form.field_values.city" :validationrule="form.validation_rules.city" :validationmsg="form.validation_messages.city" label="Town/City" autocomplete="address-level2"></c-input>
            <c-input name="county" v-model="form.field_values.county" :validationrule="form.validation_rules.county" :validationmsg="form.validation_messages.county" label="County" autocomplete="address-level1"></c-input>
            <c-input name="postcode" v-model="form.field_values.postcode" :validationrule="form.validation_rules.postcode" :validationmsg="form.validation_messages.postcode" label="Postcode" autocomplete="postal-code"></c-input>
            <c-input name="country" v-model="form.field_values.country" :validationrule="form.validation_rules.country" :validationmsg="form.validation_messages.country" label="Country" :suggestions="countries" :suggestions_model="true" type="autocomplete" autocomplete="country-name"></c-input>
            <c-input class="col-span-2:3" name="file" v-model="form.field_values.file" :validationrule="form.validation_rules.file" :validationmsg="form.validation_messages.file" label="File" type="file" endpoint="upload"></c-input>
            <c-input class="col-span-2:3" name="files" v-model="form.field_values.files" :validationrule="form.validation_rules.files" :validationmsg="form.validation_messages.files" label="Files" type="file" multiple endpoint="upload" accept="image/jpeg, image/png, image/gif, video/mp4, video/mov, video/avi"></c-input>
            <c-input class="col-span-2:3" name="message" type="textarea" v-model="form.field_values.message" :validationrule="form.validation_rules.message" :validationmsg="form.validation_messages.message" label="Message"></c-input>
        </div>

        <c-message :content="form.response.error_message" class="message--negative" :trigger="form.response.error"></c-message>
        <c-message :content="form.response.success_message" class="message--positive" :trigger="form.response.success"></c-message>

        <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Submit my details</button>
    </template>
</c-form>