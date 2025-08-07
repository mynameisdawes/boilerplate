<header class="document__header document__header--fixed document__header--transparent_not document__header--transparent_dark_not document__header--transparent_light_not" role="banner" aria-label="Document Header">
    <div class="document__navigation_strip">
        <div class="container:xl">
            <div class="content__wrapper">
                <div class="content">
                    <div class="document__logo">
                        <a href="{{ route('base') }}" aria-label="Homepage Link">
                            <img src="{{ route('logo') }}" alt="{{ config('app.company.name') }}" width="124" height="23" />
                        </a>
                    </div>
                </div>
                <div class="content">
                    <div class="document__navigation__action">
                        @if (config('shop.enabled') === true)
                            <a href="{{ route('shop.cart.index') }}" class="document__navigation__cart" aria-label="Cart Link">
                                <span class="document__navigation__cart__count" v-html="count" v-show="count && count > 0"></span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19.9" viewBox="0 0 20 19.9"><path class="fill-current" d="M16.4,3.9H15V2.2C15,1,14,0,12.8,0H7.2C6,0,5,1,5,2.2v1.6H3.6C1.6,3.9,0,5.5,0,7.5v8.9c0,2,1.6,3.6,3.6,3.6h12.9c2,0,3.6-1.6,3.6-3.6V7.5C20,5.5,18.4,3.9,16.4,3.9z M7.2,2.2h5.5v1.6H7.2V2.2z M17.6,16.3c0,0.7-0.5,1.2-1.2,1.2H3.6c-0.7,0-1.2-0.5-1.2-1.2V7.5c0-0.7,0.5-1.2,1.2-1.2h12.9c0.7,0,1.2,0.5,1.2,1.2V16.3z"/></svg>
                            </a>
                        @endif
                        @if (config('app.account.enabled') == true)
                            <div class="document__navigation__account" :class="{'document__navigation__account--logged_in': is_logged_in == true}">
                                <component class="document__navigation__account__icon" :is="is_logged_in == true ? 'div' : 'a'" :href="is_logged_in == true ? null : '{{ route('dashboard.onecrm.index') }}'" :aria-label="is_logged_in == true ? 'Account Area' : 'Account Link'">
                                    <div class="document__navigation__account__icon--tick" v-if="is_logged_in == true">
                                        <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="60px" height="60px" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve"><polygon class="fill-current" points="51.483,5.936 20.39,37.029 8.517,25.157 0,33.674 20.39,54.064 60,14.453 "/></svg>
                                    </div>
                                    <svg xmlns="http://www.w3.org/2000/svg" x="0px" y="0px" width="30px" height="30px" viewBox="0 0 30 30" style="enable-background:new 0 0 30 30;" xml:space="preserve"><path class="fill-current" d="M21.6,17.2c2-1.8,3.3-4.4,3.3-7.3C24.9,4.4,20.5,0,15,0S5.1,4.4,5.1,9.9c0,2.9,1.3,5.5,3.3,7.3c-3.1,0.8-5.6,2.2-7,4 C3.7,26.4,8.9,30,15,30s11.3-3.6,13.7-8.8C27.2,19.4,24.7,18,21.6,17.2z"/></svg>
                                </component>
                                <ul v-if="is_logged_in == true">
                                    @include('partials.dashboard_navigation')
                                </ul>
                            </div>
                        @endif
                        @if (config('app.search.enabled') == true && config('shop.only') === false)
                            <div class="document__navigation__search" @click.stop="search_trigger = !search_trigger">
                                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="19.89" viewBox="0 0 20 19.89"><path class="fill-current" d="M20,18.19l-6.34-6.34A7.54,7.54,0,1,0,12,13.56l6.33,6.33ZM3.9,11.13a5.11,5.11,0,1,1,7.23,0A5.12,5.12,0,0,1,3.9,11.13Z"></path></svg>
                            </div>
                        @endif
                        @if (config('app.color_scheme.enabled') == true)
                            <c-color_scheme>
                                <template v-slot:default="color_scheme">
                                    <div class="document__navigation__color_scheme_popup" @click.stop="color_scheme.togglePopup"><div class="color_scheme_icon">
                                        <svg :class="{ 'is_active': color_scheme.scheme_name == 'system'}" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M20,14.19V1.77H0v12.42h8.63v2.85h-3.23v2.19h9.19v-2.19h-3.23v-2.85h8.63ZM2,3.77h16v8.42H2V3.77Z"/></svg>
                                        <svg :class="{ 'is_active': color_scheme.scheme_name == 'default'}" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M10,5c-2.76,0-5,2.24-5,5s2.24,5,5,5,5-2.24,5-5-2.24-5-5-5ZM10,4c.55,0,1-.45,1-1V1c0-.55-.45-1-1-1s-1,.45-1,1v2c0,.55.45,1,1,1ZM10,16c-.55,0-1,.45-1,1v2c0,.55.45,1,1,1s1-.45,1-1v-2c0-.55-.45-1-1-1ZM19,9h-2c-.55,0-1,.45-1,1s.45,1,1,1h2c.55,0,1-.45,1-1s-.45-1-1-1ZM4,10c0-.55-.45-1-1-1H1c-.55,0-1,.45-1,1s.45,1,1,1h2c.55,0,1-.45,1-1ZM15.66,14.24c-.39-.39-1.02-.39-1.41,0-.39.39-.39,1.02,0,1.41l1.41,1.41c.39.39,1.02.39,1.41,0,.39-.39.39-1.02,0-1.41l-1.41-1.41ZM4.34,5.76c.39.39,1.02.39,1.41,0s.39-1.02,0-1.41l-1.41-1.41c-.39-.39-1.02-.39-1.41,0s-.39,1.02,0,1.41l1.41,1.41ZM4.34,14.24l-1.41,1.41c-.39.39-.39,1.02,0,1.41.39.39,1.02.39,1.41,0l1.41-1.41c.39-.39.39-1.02,0-1.41-.39-.39-1.02-.39-1.41,0ZM15.66,5.76l1.41-1.41c.39-.39.39-1.02,0-1.41s-1.02-.39-1.41,0l-1.41,1.41c-.39.39-.39,1.02,0,1.41s1.02.39,1.41,0Z"/></svg>
                                        <svg :class="{ 'is_active': color_scheme.scheme_name == 'dark'}" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M8.67,1.3c-1.55,0-3,.4-4.27,1.1,3.96.88,6.93,4.41,6.93,8.63,0,3.34-1.85,6.24-4.58,7.75.62.14,1.26.22,1.92.22,4.89,0,8.85-3.96,8.85-8.85S13.56,1.3,8.67,1.3Z"/></svg>
                                    </div>
                                        <div class="popup" :class="{ 'is_open': color_scheme.popup }" @click.stop>
                                            <ul class="color_schemes">
                                                <li :class="{ 'is_active': color_scheme.scheme_name == 'system'}" @click="color_scheme.setColorScheme('system')"><div class="color_scheme_icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M20,14.19V1.77H0v12.42h8.63v2.85h-3.23v2.19h9.19v-2.19h-3.23v-2.85h8.63ZM2,3.77h16v8.42H2V3.77Z"/></svg></div><span>System</span></li>
                                                <li :class="{ 'is_active': color_scheme.scheme_name == 'default'}" @click="color_scheme.setColorScheme('default')"><div class="color_scheme_icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M10,5c-2.76,0-5,2.24-5,5s2.24,5,5,5,5-2.24,5-5-2.24-5-5-5ZM10,4c.55,0,1-.45,1-1V1c0-.55-.45-1-1-1s-1,.45-1,1v2c0,.55.45,1,1,1ZM10,16c-.55,0-1,.45-1,1v2c0,.55.45,1,1,1s1-.45,1-1v-2c0-.55-.45-1-1-1ZM19,9h-2c-.55,0-1,.45-1,1s.45,1,1,1h2c.55,0,1-.45,1-1s-.45-1-1-1ZM4,10c0-.55-.45-1-1-1H1c-.55,0-1,.45-1,1s.45,1,1,1h2c.55,0,1-.45,1-1ZM15.66,14.24c-.39-.39-1.02-.39-1.41,0-.39.39-.39,1.02,0,1.41l1.41,1.41c.39.39,1.02.39,1.41,0,.39-.39.39-1.02,0-1.41l-1.41-1.41ZM4.34,5.76c.39.39,1.02.39,1.41,0s.39-1.02,0-1.41l-1.41-1.41c-.39-.39-1.02-.39-1.41,0s-.39,1.02,0,1.41l1.41,1.41ZM4.34,14.24l-1.41,1.41c-.39.39-.39,1.02,0,1.41.39.39,1.02.39,1.41,0l1.41-1.41c.39-.39.39-1.02,0-1.41-.39-.39-1.02-.39-1.41,0ZM15.66,5.76l1.41-1.41c.39-.39.39-1.02,0-1.41s-1.02-.39-1.41,0l-1.41,1.41c-.39.39-.39,1.02,0,1.41s1.02.39,1.41,0Z"/></svg></div><span>Light</span></li>
                                                <li :class="{ 'is_active': color_scheme.scheme_name == 'dark'}" @click="color_scheme.setColorScheme('dark')"><div class="color_scheme_icon"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20"><path class="fill-current" d="M8.67,1.3c-1.55,0-3,.4-4.27,1.1,3.96.88,6.93,4.41,6.93,8.63,0,3.34-1.85,6.24-4.58,7.75.62.14,1.26.22,1.92.22,4.89,0,8.85-3.96,8.85-8.85S13.56,1.3,8.67,1.3Z"/></svg></div><span>Dark</span></li>
                                            </ul>
                                        </div>
                                    </div>
                                </template>
                            </c-color_scheme>
                        @endif
                        <button class="document__navigation__icon" :class="{
                            'is_open': slider_trigger,
                            'document__navigation__icon--r': navigation_mode == 'r', 'document__navigation__icon--sm': navigation_mode == 'sm', 'document__navigation__icon--lg': navigation_mode == 'lg'
                        }" @click.stop="toggleNavigation" v-if="navigation">
                            <div class="navigation_icon">
                                <span class="navigation_icon__el navigation_icon__el--top"></span>
                                <span class="navigation_icon__el navigation_icon__el--middle"></span>
                                <span class="navigation_icon__el navigation_icon__el--bottom"></span>
                            </div>
                        </button>
                    </div>
                    <c-slider class="from_top_not full_not" :trigger="slider_trigger" :mode="navigation_mode" v-if="navigation">
                        <c-navigation ref="navigation" :mode="navigation_mode" @close="closeNavigation" :items="navigation_items"></c-navigation>
                        {{-- <c-navigation class="document__navigation__links_lg_not" ref="navigation" mode="lg" @close="closeNavigation" :items="navigation_items"></c-navigation> --}}
                    </c-slider>
                </div>
            </div>
        </div>
    </div>
</header>