<div class="builder__wrapper grid grid-chldrn-fw mb-6">
    <c-steps @submit="productScope.addCartItem()" @step_change="productScope.setActiveStep">
        <template v-slot:steps_progress="steps">
            {{-- <div class="timeline" :style="{'--_timeline_progress': (steps.active_step - 1) / (steps.steps.length - 1) * 100}">
                <div class="timeline-background">
                    <div class="timeline-point" v-for="step in steps.steps"></div>
                </div>
                <div class="timeline-foreground">
                    <div class="timeline-point" v-for="step in steps.steps"></div>
                </div>
            </div> --}}
            <c-colour_select v-if="productScope.variations.colour != null" :product="productScope"></c-colour_select>
        </template>
        <template v-slot:steps_content="steps">
            <div class="steps__wrapper order-2:1t2e py-4:3 mx-auto">
                <c-step :active_step="steps.active_step" :display_step="1" name="Garment Details">
                    <template v-slot:default="step">
                        <c-print_locations :product="productScope" :validate="productScope.options.customisations"></c-print_locations>
                        <c-message content="<p><strong>Please Note:</strong> Choose your print locations and upload your artwork, logos or images. <strong>Once uploaded</strong> you can <strong>move / scale / rotate</strong> in the <u>next step</u>.</p><p><strong>TIP:</strong> High-res, transparent PNG images work best for artwork and logos.</p>" :trigger="true" :autohide="false" rememberhide="artwork_guidelines"></c-message>
                    </template>
                </c-step>
                <c-step :active_step="steps.active_step" :display_step="2" name="Print Spec">
                    <template v-slot:default="step">
                        <c-orientation :product="productScope" :validate="productScope.getCanvasError()"></c-orientation>
                    </template>
                </c-step>
                <c-step :active_step="steps.active_step" :display_step="3" name="Summary">
                    <template v-slot:default="step">
                        <div class="mx-auto">
                            <div class="grid grid-cols-2:3 grid-cols-1">
                                <div v-for="(location, name) in productScope.options.customisations" class="mb-4:1e">
                                    <h4 class="mb-1:1e">@{{ location.label }}</h4>
                                    <p v-if="!location.selected">No @{{ location.label }} print</p>
                                    <ul v-else>
                                        <li>@{{ location.position }} Print</li>
                                        <li>@{{ parseInt(location.dimensions.w) }} x @{{ parseInt(location.dimensions.h) }}mm</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="py-4">
                                <button class="btn--reset--hard add-note pointer" @click="productScope.openNote">
                                    {!! file_get_contents(public_path('/assets/img/icons/icon__pencil.svg')) !!}
                                    <span class="text-light">@{{ productScope.options.note && productScope.options.note !== '' ? 'Edit' : 'Add' }} Note</span>
                                </button>
                            </div>
                            <h3 class="font-normal">Pick your size@{{ productScope.multi_select ? 's' : '' }}</h3>
                            <template v-if="productScope.multi_select">
                                <c-size_multi_select :can_remove="productScope.selected_sorted.length > 1" :product="productScope" @option_removed="productScope.removeOption"></c-size_multi_select>
                            </template>
                            <template v-else>
                                <c-size_select :product="productScope" :validate="productScope.options[productScope.secondary]"></c-size_select>
                                <c-quantity :product="productScope" :validate="productScope.qty"></c-quantity>
                            </template>
                        </div>
                    </template>
                </c-step>
                <div v-if="!productScope.hide_pricing" class="pricing mt-4">
                    <div class="text-right">
                        <p class="mb-0"><span class="pr-1">(exc. VAT)</span><span class="pl-1 font-bold">@{{ productScope.formatPrice(productScope.price_per_unit) }}</span></p>
                        <p class="my-0"><span class="pr-1">(Unit price inc. VAT)</span><span class="pl-1 font-bold" style="font-size: 2rem;">@{{ productScope.formatPrice(productScope.display_price) }}</span></p>
                    </div>
                </div>
            </div>
        </template>
        <template v-slot:steps_navigation="steps">
            <c-message v-for="error in steps.v$.$errors" :content="error.$message" :required="true" :autohide="false" class="message--negative message--top" :trigger="steps.v$.$error"></c-message>
            <div class="document__footer__actions">
                <div class="document__navigation_strip">
                    <div class="container:xl">
                        <div class="content__wrapper">
                            <div class="content">
                                <a v-if="steps.active_step == 1" href="{{ route('shop.product.index') }}" class="btn border-transparent text-sm underline">Back to shop</a>
                                <a v-else @click="steps.prevStep" class="btn border-transparent text-sm underline">@{{ steps.steps[steps.active_step - 1].name }}</a>
                            </div>
                            <div class="content">
                                <button v-if="steps.active_step != steps.step_count" @click="steps.nextStep" :class="{ is_disabled: steps.is_disabled }" class="btn bg-primary border-primary text-primary_contrasting">Next</button>
                                <button @click="steps.submit" v-else type="submit" :class="{ is_disabled: steps.is_disabled }" class="btn bg-primary border-primary text-primary_contrasting">Add to Cart</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </c-steps>
</div>
<c-modal :trigger="productScope.note_trigger" @open="productScope.openNote" @close="productScope.closeNote">
    <h3>@{{ productScope.options.note && productScope.options.note !== '' ? 'Edit' : 'Add' }} note</h3>
    <c-input name="additional_note" type="textarea" v-model="productScope.note.tmp"></c-input>
    <button :class="{ is_disabled: productScope.note.tmp == '' && (!productScope.options.note || (productScope.options.note && productScope.options.note == '')) }" class="btn btn--pill btn--bg_primary btn--o_hover btn--f btn--wide tm--sm--1e tm--md--2e loud" @click="productScope.saveNote">Save</button>
</c-modal>