<component :is="'{{ $customisable ? 'c-customisable' : 'c-product' }}'" :config="{{ $product }}" @update:cart="updateCart" :base_customisations="{{ $customisations ?? "null" }}">
    <template v-slot:default="productScope">
        <article class="product">
            <div class="spinner__wrapper" :class="{ is_loading: productScope.is_loading == true }">
                <div class="spinner"></div>
            </div>
            <c-message :content="productScope.success_message" class="message--positive message--top" :trigger="productScope.is_success_message_shown" :autohide="true"></c-message>
            <c-message :content="productScope.error_message" class="message--negative message--top" :trigger="productScope.is_error_message_shown" :autohide="true"></c-message>
            @if (config('shop.single_product_slug') === null)
                <div class="document__header__actions">
                    <div class="document__navigation_strip">
                        <div class="container:xl">
                            <div class="content__wrapper">
                                <div class="content">
                                    <ul class="breadcrumbs" v-show="productScope.is_ready">
                                        <li><a href="{{ route('shop.product.index') }}">Back to Shop</a></li>
                                        <li v-html="productScope.config.name_label"></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="container:xl">
                <div class="grid:3 grid-cols-2:3 gap-4 gap-5:2 gap-12:3" v-show="productScope.is_ready">
                    <header class="mb-4 mb-6:1t2e">
                        <h1 class="text-gradient text-center:1t2e mx-auto:1t2e" v-html="productScope.config.name_label"></h1>
                        <div class="text-center:1t2e -mt-2"><small>@{{ productScope.sku }}</small></div>

                        <c-countdown_timer v-if="productScope.config.configuration.launch_date" :target-date="productScope.config.configuration.launch_date">
                            <template v-slot:default="countdownScope">
                                <div class="countdown" v-if="!countdownScope.reached_end">
                                    <div>
                                        <span v-if="!countdownScope.less_than_day">@{{ countdownScope.days }} <small>@{{ countdownScope.days == 1 ? 'day' : 'days' }}</small>, @{{ countdownScope.hours }} <small>@{{ countdownScope.hours == 1 ? 'hr' : 'hrs' }}</small></span>
                                        <span v-else>@{{ countdownScope.hours_padded }} <small>@{{ countdownScope.hours == 1 ? 'hr' : 'hrs' }}</small>, @{{ countdownScope.minutes_padded }} <small>@{{ countdownScope.minutes == 1 ? 'min' : 'mins' }}</small>, @{{ countdownScope.seconds_padded }} <small>@{{ countdownScope.seconds == 1 ? 'sec' : 'secs' }}</small></span>
                                    </div>
                                    <p>This garment is on preorder to maximise profit distribution to charity and minimise waste!</p>
                                    <p><strong>Please note:</strong> Although payment will be taken now, production will begin when the countdown ends.</p>
                                </div>
                            </template>
                        </c-countdown_timer>
                    </header>
                    @include($partials . ".gallery" . ($multi_select ? "_multiselect" : "" ))

                    <div class="details">
                        <span class="badge mx-auto:1t2e mb-4:3" v-if="productScope.config.configuration.gender">@{{ productScope.config.configuration.gender }}</span>

                        @include($partials . ".variations")

                        <c-tabs class="mb-4" :active_tab="active_tab" @active-tab="setActiveTab" v-if="productScope.config.configuration.description || productScope.config.configuration.size_guide || productScope.config.configuration.shipping">
                            <template v-slot:default="tabs">
                                <c-tab name="1" label="Details" v-if="productScope.config.configuration.description">
                                    <div class="text-sm my-3" v-html="productScope.config.configuration.description"></div>
                                </c-tab>
                                <c-tab name="2" label="Size Guide" v-if="productScope.config.configuration.size_guide">
                                    <div class="text-sm mt-3" v-if="productScope.config.configuration.size_guide">
                                        <table>
                                            <tbody>
                                                <tr v-for="tr in productScope.config.configuration.size_guide">
                                                    <td v-for="td in tr">@{{ td }}</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="text-sm my-3" v-if="productScope.config.configuration.size_guide_note">
                                        <span class="block font-bold text-rose-600">Please note</span>
                                        <p>@{{ productScope.config.configuration.size_guide_note }}</p>
                                    </div>
                                </c-tab>
                                <c-tab name="3" label="Shipping" v-if="productScope.config.configuration.shipping">
                                    <div class="text-sm my-3" v-html="productScope.config.configuration.shipping"></div>
                                </c-tab>
                            </template>
                        </c-tabs>
                    </div>
                </div>
            </div>
        </article>
    </template>
</component>