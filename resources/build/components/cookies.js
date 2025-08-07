import _config from "@/utilities/config.js";
_config.init();

import { _api } from "@/utilities/api.js";

import { CookieStorage } from "cookie-storage";
const cookieStorage = new CookieStorage();

import _utilities from "@/utilities/utilities.js";
import _validation from "@/utilities/validation.js";

import _overlay_mixin from "@/mixins/overlays.js";

let _cookies = {
    name: "c-cookies",
    emits: [
        "hide_consent",
        "show_consent",
        "update_consent",
    ],
    data() {
        return {
            active_tab: "customise",
            categories: {
                strict: {
                    show_extra_info: false,
                    label: "Necessary",
                    el_id: "CookieConsentTypeNecessary",
                    accepted: true,
                    providers: [
                        {
                            label: "Vektor",
                            link: "https://vektor.co.uk/cookies",
                            cookies: [
                                {
                                    label: "cookie_consent",
                                    description: "We need this"
                                }
                            ]
                        }
                    ]
                },
                functionality: {
                    show_extra_info: false,
                    label: "Preferences",
                    el_id: "CookieConsentTypePreferences",
                    accepted: false,
                    cookies: {}
                },
                targeting: {
                    show_extra_info: false,
                    label: "Marketing",
                    el_id: "CookieConsentTypeMarketing",
                    accepted: false,
                    cookies: {}
                },
                performance: {
                    show_extra_info: false,
                    label: "Statistics",
                    el_id: "CookieConsentTypeStatistics",
                    accepted: false,
                    cookies: {}
                },
                // unclassified: {
                //     label: "Other",
                //     accepted: false,
                //     cookies: {}
                // }
            }
        };
    },
    methods: {
        toggleCookie(name) {
            this.categories[name].accepted = !this.categories[name].accepted;
        },
        allCookies(choice) {
            let cookies = this.categories;

            Object.keys(cookies).forEach(cookie => {
                if (cookie != "strict") cookies[cookie].accepted = choice;
            });

            this.updateCookies();
        },
        updateCookies() {
            let cookieContent = "date:" + _utilities.timestamp();
            let categories = this.categories;
            const date = new Date();
            date.setFullYear(date.getFullYear() + 1);

            Object.keys(categories).forEach(category => {
                cookieContent += "," + category + ":" + categories[category].accepted
            });

            cookieStorage.setItem("cookie_consent", cookieContent, {
                path: "/",
                expires: date
            });

            this.$emit("update_consent");
        },
        setActiveTab(tab_name) {
            this.active_tab = tab_name;
        },
        toggleAccordion(event) {
            const el = event.currentTarget.parentNode.parentNode;
            el.classList.toggle("open");
        }
    },
    mixins: [_overlay_mixin],
    template: `
    <div class="modal__overlay modal__cookies from_bottom" :aria-hidden="!is_open" :class="[{ is_open: is_open == true }]" @click.stop="attemptCloseAction">
        <div class="modal__dialog">
            <div class="modal__inner" @click.stop>
                <div class="modal__dismiss" @click.stop="attemptCloseAction" v-if="!required"></div>
                <div class="modal__content">
                    <slot
                        :allCookies="allCookies"
                        :updateCookies="updateCookies"
                        :toggleCookie="toggleCookie"
                        :toggleAccordion="toggleAccordion"
                        :setActiveTab="setActiveTab"
                        :categories="categories"
                        :active_tab="active_tab"
                    ></slot>
                </div>
                <div class="modal__content__after">
                    <slot name="after_content"></slot>
                </div>
            </div>
        </div>
    </div>
    `
};

export default _cookies;