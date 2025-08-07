@php
$discount = Vektor\Shop\Models\Discount::where('id', $discount_id)->first();
@endphp
@if ($discount && !empty($discount->amount))
    @if ($discount->type == 'percentage')
        <div class="promo_cta">
            <div class="promo_cta_primary">
                <header>
                    <p class="subtitle">Promotion</p>
                    @if (!empty($discount->name))
                        <p class="title">{{ $discount->name }}</p>
                    @endif
                </header>
                @if (!empty($discount->blurb))
                    <p>{{ $discount->blurb }}</p>
                @endif
                <c-discount_promo discount_id="{{ $discount->id }}">
                    <template v-slot:default="promoScope">
                        <div class="spinner__wrapper " :class="{ is_loading: promoScope.is_loading == true }">
                            <div class="spinner"></div>
                        </div>
                        <c-form :name="promoScope.ref_name" :ref="promoScope.ref_name" method="post" :field_values="promoScope.field_values" :field_storage="promoScope.field_storage" :field_validation_rules="promoScope.validation_rules" :field_validation_messages="promoScope.validation_messages" @success="promoScope.register">
                            <template v-slot:fields="form">
                                <c-message :content="promoScope.error_message" class="message--negative" :trigger="promoScope.is_error_message_shown" :autohide="true"></c-message>
                                <c-message :content="promoScope.success_message" class="message--positive" :trigger="promoScope.is_success_message_shown" :autohide="true"></c-message>
                                <div class="promo_wrapper">
                                    <c-input name="email" v-model="form.field_values.email" :validationrule="form.validation_rules.email" :validationmsg="form.validation_messages.email" label="Email" autocomplete="email" type="email" placeholder="Enter email"></c-input>
                                    <button type="submit" class="btn bg-secondary border-secondary text-secondary_contrasting" :class="{ is_disabled: form.v$.$invalid == true }">Send</button>
                                </div>
                            </template>
                        </c-form>
                    </template>
                </c-discount_promo>
            </div>
            @if (!empty($discount->cta_url) && !empty($discount->cta_text))
                <div class="promo_cta_secondary">
                    <div class="collection"><a class="btn bg-background border-background text-background_contrasting" href="{{ $discount->cta_url }}">{{ $discount->cta_text }}</a></div>
                </div>
            @endif
        </div>
    @endif
@endif