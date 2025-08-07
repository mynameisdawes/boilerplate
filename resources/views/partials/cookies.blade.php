<c-cookies v-slot:default="cookieScope" :trigger='show_cookie_consent' @show_consent="show_cookie_consent = true" @hide_consent="show_cookie_consent = false" @update_consent="checkConsent" :required="true">
    <c-tabs class="mb-4" :active_tab="cookieScope.active_tab" @active-tab="cookieScope.setActiveTab">
        <template v-slot:default="cookieTabs">
            <c-tab name="consent" label="Consent">
                <p>
                    <strong>This website uses cookies</strong>
                    <br />
                    We use necessary cookies to make our site work. We'd also like to set analytics cookies that help us make improvements by measuring how you use the site. These will be set only if you accept.
                    <br />
                    For more detailed information about the cookies we use, see our Cookies page. <a href="/privacy#cookies">Cookie Policy</a>
                </p>
                <div class="flex flex-wrap gap-2">
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(false)" class="btn flex-grow border-primary text-xs">Deny</a>
                    <a href="#" @click.stop.prevent="cookieScope.setActiveTab('customise')" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Customise</a>
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(true)" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Accept All</a>
                </div>
            </c-tab>
            <c-tab name="customise" label="Details">
                <div v-for="(category, type) in cookieScope.categories" class="cookie_consent_wrapper type pl-4 mb-4" :class="{ open: category.show_extra_info == true }">
                    <header class="flex justify-between">
                        <a href="#" @click.stop.prevent="category.show_extra_info = !category.show_extra_info">
                            <p :class="{ 'label': category.providers && category.providers.length > 0 }" v-html="category.label"></p>
                        </a>

                        <div class="field__wrapper">
                            <label class="switch">
                                <input :aria-label="category.label" :name="type" :disabled="type == 'strict'" type="checkbox" @change="cookieScope.toggleCookie(type)" :checked="category.accepted">
                            </label>
                        </div>
                    </header>

                    <div v-for="provider in category.providers" class="cookie_consent_wrapper provider" v-if="category.providers && category.providers.length > 0">
                        <header>
                            <a href="#" @click.stop.prevent="category.show_extra_info = !category.show_extra_info">
                                <p v-html="provider.label"></p>
                            </a>
                            <a :href="provider.link" v-if="provider.link">Learn more about this provider</a>
                        </header>
                        <div v-for="cookie in provider.cookies" class="cookie_consent_wrapper cookie">
                            <p v-html="cookie.label + ' : ' + cookie.description"></p>
                        </div>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(false)" class="btn flex-grow border-primary text-xs">Deny</a>
                    <a href="#" @click.stop.prevent="cookieScope.updateCookies()" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Allow Selection</a>
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(true)" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Accept All</a>
                </div>
            </c-tab>
            <c-tab name="about" label="About">
                <p>Cookies are small text files that can be used by websites to make a user's experience more efficient.</p>
                <p>The law states that we can store cookies on your device if they are strictly necessary for the operation of this site. For all other types of cookies we need your permission.</p>
                <p>This site uses different types of cookies. Some cookies are placed by third party services that appear on our pages.</p>
                <p>You can at any time change or withdraw your consent from the Cookie Declaration on our website.</p>
                <p>Learn more about who we are, how you can contact us and how we process personal data in our Privacy Policy.</p>
                <div class="flex flex-wrap gap-2">
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(false)" class="btn flex-grow border-primary text-xs">Deny</a>
                    <a href="#" @click.stop.prevent="cookieScope.setActiveTab('customise')" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Customise</a>
                    <a href="#" @click.stop.prevent="cookieScope.allCookies(true)" class="btn flex-grow bg-primary border-primary text-primary_contrasting text-xs">Accept All</a>
                </div>
            </c-tab>
        </template>
    </c-tabs>
</c-cookies>