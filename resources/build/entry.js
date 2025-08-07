import './node_modules/nouislider/dist/nouislider.css';
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/thumbs";
import "../sass/style.scss";

// Synchronous config import
import _config from "./utilities/config.js";
_config.init();

let _imports = [];
let _vue_components = {};

// Asynchronous 3rd party imports
let CookieStorage;
_imports.push(import("cookie-storage").then((exports) => { CookieStorage = exports.CookieStorage; }));

let vue_modules = {};
_imports.push(import("vue").then((exports) => {
    vue_modules["createApp"] = exports.createApp;
}));

let _validation_helpers;
_imports.push(import("@vuelidate/validators").then((exports) => { _validation_helpers = exports.helpers; }));

// Asynchronous 1st party imports
let _api, _storage;
_imports.push(import("./utilities/api.js").then((exports) => {
    _api = exports._api;
    _storage = exports._storage;
}));

let _validation;
_imports.push(import("./utilities/validation.js").then((exports) => { _validation = exports.default; }));

// Asynchronous 3rd party component imports
let _swiper_modules = [];
_imports.push(import("swiper/modules").then((exports) => {
    _swiper_modules.push(exports.Navigation);
    _swiper_modules.push(exports.Pagination);
    _swiper_modules.push(exports.Thumbs);
}));

_imports.push(import("swiper/vue").then((exports) => {
    _vue_components["Swiper"] = exports.Swiper;
    _vue_components["SwiperSlide"] = exports.SwiperSlide;
}));

// Asynchronous 1st party component imports
if (_config.get("app.color_scheme.enabled")) {
    _imports.push(import("./components/color_scheme.js").then((exports) => { _vue_components["c-color_scheme"] = exports.default; }));
}
_imports.push(import("./components/contents.js").then((exports) => { _vue_components["c-contents"] = exports.default; }));
_imports.push(import("./components/cookies.js").then((exports) => { _vue_components["c-cookies"] = exports.default; }));
_imports.push(import("./components/form.js").then((exports) => { _vue_components["c-form"] = exports.default; }));
_imports.push(import("./components/form_segment.js").then((exports) => { _vue_components["c-form_segment"] = exports.default; }));
_imports.push(import("./components/input.js").then((exports) => { _vue_components["c-input"] = exports.default; }));
_imports.push(import("./components/map.js").then((exports) => { _vue_components["c-map"] = exports.default; }));
_imports.push(import("./components/message.js").then((exports) => { _vue_components["c-message"] = exports.default; }));
_imports.push(import("./components/navigation.js").then((exports) => { _vue_components["c-navigation"] = exports.default; }));
_imports.push(import("./components/overlays.js").then((exports) => {
    _vue_components["c-modal"] = exports._modal;
    _vue_components["c-confirmation"] = exports._confirmation;
}));
_imports.push(import("./components/pagination.js").then((exports) => { _vue_components["c-pagination"] = exports.default; }));
_imports.push(import("./components/panel_expand.js").then((exports) => { _vue_components["c-panel_expand"] = exports.default; }));

if (_config.get("app.search.enabled")) {
    _imports.push(import("./components/search.js").then((exports) => { _vue_components["c-search"] = exports.default; }));
}
_imports.push(import("./components/slider.js").then((exports) => { _vue_components["c-slider"] = exports.default; }));
_imports.push(import("./components/tabs.js").then((exports) => {
    _vue_components["c-tab"] = exports._tab;
    _vue_components["c-tabs"] = exports._tabs;
}));

if (_config.get("onecrm.enabled")) {
    _imports.push(import("./components/onecrm_dashboard.js").then((exports) => { _vue_components["c-onecrm_dashboard"] = exports.default; }));
    _imports.push(import("./components/onecrm_orders.js").then((exports) => { _vue_components["c-onecrm_orders"] = exports.default; }));
    _imports.push(import("./components/onecrm_order.js").then((exports) => { _vue_components["c-onecrm_order"] = exports.default; }));
}

_imports.push(import("./components/discount_promo.js").then((exports) => { _vue_components["c-discount_promo"] = exports.default; }));

if (_config.get("shop.enabled")) {
    _imports.push(import("./components/cart.js").then((exports) => { _vue_components["c-cart"] = exports.default; }));
    _imports.push(import("./components/saved_carts.js").then((exports) => { _vue_components["c-saved_carts"] = exports.default; }));
    _imports.push(import("./components/user_addresses.js").then((exports) => { _vue_components["c-user_addresses"] = exports.default; }));
    _imports.push(import("./components/checkout.js").then((exports) => { _vue_components["c-checkout"] = exports.default; }));
    _imports.push(import("./components/product_simple.js").then((exports) => { _vue_components["c-product"] = exports.default; }));
    _imports.push(import("./components/product_customisable.js").then((exports) => { _vue_components["c-customisable"] = exports.default; }));
    _imports.push(import("./components/products.js").then((exports) => { _vue_components["c-products"] = exports.default; }));
    _imports.push(import("./components/product_attributes.js").then((exports) => { _vue_components["c-product_attributes"] = exports.default; }));
    if (_config.get("payments.account.enabled")) {
        _imports.push(import("./components/payment_account.js").then((exports) => { _vue_components["c-payment_account"] = exports.default; }));
    }
    if (_config.get("payments.cash.enabled")) {
        _imports.push(import("./components/payment_cash.js").then((exports) => { _vue_components["c-payment_cash"] = exports.default; }));
    }
    if (_config.get("payments.purchase_order.enabled")) {
        _imports.push(import("./components/payment_purchase_order.js").then((exports) => { _vue_components["c-payment_purchase_order"] = exports.default; }));
    }
    if (_config.get("payments.paypal.enabled")) {
        _imports.push(import("./components/payment_paypal.js").then((exports) => { _vue_components["c-payment_paypal"] = exports.default; }));
    }
    if (_config.get("payments.stripe.enabled")) {
        _imports.push(import("./components/payment_stripe.js").then((exports) => { _vue_components["c-payment_stripe"] = exports.default; }));
        _imports.push(import("./components/payment_stripe_cards.js").then((exports) => { _vue_components["c-payment_stripe_cards"] = exports.default; }));
        _imports.push(import("./components/cards.js").then((exports) => { _vue_components["c-cards"] = exports.default; }));
    }
    if (_config.get("payments.stripe.request.enabled")) {
        _imports.push(import("./components/payment_stripe_request.js").then((exports) => { _vue_components["c-payment_stripe_request"] = exports.default; }));
    }

    if (_config.get("onecrm.enabled")) {
        _imports.push(import("./components/onecrm_quote.js").then((exports) => { _vue_components["c-onecrm_quote"] = exports.default; }));
        _imports.push(import("./components/checkout_quote.js").then((exports) => { _vue_components["c-checkout_quote"] = exports.default; }));
    }
}

_imports.push(import("./components/countdown_timer.js").then((exports) => { _vue_components["c-countdown_timer"] = exports.default; }));

_imports.push(import("./components/tooltip.js").then((exports) => { _vue_components["c-tooltip"] = exports.default; }));

_imports.push(import("./components/design_canvas.js").then((exports) => { _vue_components["c-design_canvas"] = exports.default; }));

_imports.push(import("./components/product_options/colour_select.js").then((exports) => { _vue_components["c-colour_select"] = exports.default; }));
_imports.push(import("./components/product_options/print_locations.js").then((exports) => { _vue_components["c-print_locations"] = exports.default; }));
_imports.push(import("./components/product_options/orientation.js").then((exports) => { _vue_components["c-orientation"] = exports.default; }));
_imports.push(import("./components/product_options/size_multi_select.js").then((exports) => { _vue_components["c-size_multi_select"] = exports.default; }));
_imports.push(import("./components/product_options/secondary_table.js").then((exports) => { _vue_components["c-secondary_table"] = exports.default; }));

-// size_select + quantity could be removed if validation is moved into input component rather than wrapping form/product element
-_imports.push(import("./components/product_options/size_select.js").then((exports) => { _vue_components["c-size_select"] = exports.default; }));
-_imports.push(import("./components/product_options/quantity.js").then((exports) => { _vue_components["c-quantity"] = exports.default; }));


_imports.push(import("./components/steps.js").then((exports) => { _vue_components["c-steps"] = exports.default; }));
_imports.push(import("./components/step.js").then((exports) => { _vue_components["c-step"] = exports.default; }));

_imports.push(import("./components/cms_posts.js").then((exports) => { _vue_components["c-cms_posts"] = exports.default; }));
_imports.push(import("./components/cms_events.js").then((exports) => { _vue_components["c-cms_events"] = exports.default; }));


_imports.push(import("./components/card_list.js").then((exports) => { _vue_components["c-card_list"] = exports.default; }));
_imports.push(import("./components/cms_cards.js").then((exports) => { _vue_components["c-cms_cards"] = exports.default; }));

Promise.all(_imports).then(() => {
    const cookieStorage = new CookieStorage();

    let vm = null;

    let fields = {
        register: {
            first_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            last_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            },
            password: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        minLength: _validation.rules.minLength(8)
                    }
                }
            },
            password_confirmation: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        sameAs: _validation_helpers.withParams(
                            { type: "sameAs" },
                            function(value, context) {
                                if (value == "") {
                                    return true;
                                } else {
                                    if (context.password == "") {
                                        return true;
                                    }
                                    if (context.password == value) {
                                        return true;
                                    }
                                    return false;
                                }
                            }
                        )
                    },
                    messages: {
                        sameAs: "This confirmation password must match"
                    }
                }
            },
        },
        login: {
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            },
            password: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        minLength: _validation.rules.minLength(8)
                    }
                }
            },
            remember_me: {
                default: true
            }
        },
        passwordless_register: {
            first_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            last_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            },
        },
        passwordless_login: {
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            }
        },
        password_email: {
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            }
        },
        password_update: {
            token: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                },
                default: _config.get("forms.password_update.token")
            },
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                },
                default: _config.get("forms.password_update.email")
            },
            password: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        minLength: _validation.rules.minLength(8)
                    }
                }
            },
            password_confirmation: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        sameAs: _validation_helpers.withParams(
                            { type: "sameAs" },
                            function(value, context) {
                                if (value == "") {
                                    return true;
                                } else {
                                    if (context.password == "") {
                                        return true;
                                    }
                                    if (context.password == value) {
                                        return true;
                                    }
                                    return false;
                                }
                            }
                        )
                    },
                    messages: {
                        sameAs: "This confirmation password must match"
                    }
                }
            },
        },
        contact: {
            title: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            first_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            last_name: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            email: {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        email: _validation.rules.email
                    }
                }
            },
            agree_marketing: {
                default: false
            },
            light: {
                default: true
            },
            phone: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            callback: {
                default: "no"
            },
            address_line_1: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            address_line_2: {},
            city: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            county: {},
            postcode: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            country: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            file: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            files: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            },
            message: {
                validations: {
                    rules: {
                        required: _validation.rules.required
                    }
                }
            }
        },
    };

    if (_config.get("shop.minimum_country_qty")) {
        fields.register.shipping_country = {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        };
    }

    vm = vue_modules.createApp({
        components: _vue_components,
        data() {
            return {
                swiper: null,
                thumbs_swiper: null,
                swiper_modules: _swiper_modules,
                swiper_pagination_options: {
                    clickable: true,
                    type: "bullets",
                    dynamicBullets: true,
                    dynamicMainBullets: 4
                },
                swiper_navigation_options: {
                    prevEl: '.swiper-button-prev',
                    nextEl: '.swiper-button-next',
                },
                swiper_breakpoints: {
                    0: {
                        slidesPerView: 1.25,
                        spaceBetween: 16,
                    },
                    640: {
                        slidesPerView: 2.25,
                        spaceBetween: 16,
                    },
                },
                titles: [ { value: "Mr", text: "Mr" }, { value: "Mrs", text: "Mrs" }, { value: "Miss", text: "Miss" } ],
                title_alternative: null,
                countries: [],
                is_logged_in: _config.get("user.is_logged_in"),
                is_loading: false,
                year: new Date().getFullYear(),
                modal_trigger: false,
                confirmation_trigger: false,
                slider_trigger: false,
                navigation_items: _config.get("navigation_items"),
                search_trigger: false,
                s: "",
                active_tab: "1",
                success_message: "",
                error_message: "",
                is_success_message_shown: false,
                is_error_message_shown: false,
                show_cookie_consent: false,
                cookiePreferences: {
                    allowAds: false,
                    allowAnalytics: false,
                    allowNecessary: true,
                    allowPersonalization: false,
                    allowPreferences: false
                },
                forms: {
                    test: {
                        ref: "test",
                        action: "api.test",
                        field_values: _validation.createFieldsData(fields.test),
                        field_storage: _validation.createFieldsStorage(fields.test),
                        validation_rules: _validation.createFieldsValidationRules(fields.test),
                        validation_messages: _validation.createFieldsValidationMessages(fields.test),
                    },
                    register: {
                        ref: "register",
                        action: "api.register",
                        field_values: _validation.createFieldsData(fields.register),
                        field_storage: _validation.createFieldsStorage(fields.register),
                        validation_rules: _validation.createFieldsValidationRules(fields.register),
                        validation_messages: _validation.createFieldsValidationMessages(fields.register),
                    },
                    login: {
                        ref: "login",
                        action: "api.login",
                        field_values: _validation.createFieldsData(fields.login),
                        field_storage: _validation.createFieldsStorage(fields.login),
                        validation_rules: _validation.createFieldsValidationRules(fields.login),
                        validation_messages: _validation.createFieldsValidationMessages(fields.login),
                    },
                    passwordless_register: {
                        ref: "passwordless_register",
                        action: "api.passwordless.register",
                        field_values: _validation.createFieldsData(fields.passwordless_register),
                        field_storage: _validation.createFieldsStorage(fields.passwordless_register),
                        validation_rules: _validation.createFieldsValidationRules(fields.passwordless_register),
                        validation_messages: _validation.createFieldsValidationMessages(fields.passwordless_register),
                    },
                    passwordless_login: {
                        ref: "passwordless_login",
                        action: "api.passwordless.login",
                        field_values: _validation.createFieldsData(fields.passwordless_login),
                        field_storage: _validation.createFieldsStorage(fields.passwordless_login),
                        validation_rules: _validation.createFieldsValidationRules(fields.passwordless_login),
                        validation_messages: _validation.createFieldsValidationMessages(fields.passwordless_login),
                    },
                    password_email: {
                        ref: "password_email",
                        action: "api.password.email",
                        field_values: _validation.createFieldsData(fields.password_email),
                        field_storage: _validation.createFieldsStorage(fields.password_email),
                        validation_rules: _validation.createFieldsValidationRules(fields.password_email),
                        validation_messages: _validation.createFieldsValidationMessages(fields.password_email),
                    },
                    password_update: {
                        ref: "password_update",
                        action: "api.password.update",
                        field_values: _validation.createFieldsData(fields.password_update),
                        field_storage: _validation.createFieldsStorage(fields.password_update),
                        validation_rules: _validation.createFieldsValidationRules(fields.password_update),
                        validation_messages: _validation.createFieldsValidationMessages(fields.password_update),
                    },
                    contact: {
                        ref: "contact",
                        action: "api.contact.submit",
                        field_values: _validation.createFieldsData(fields.contact),
                        field_storage: _validation.createFieldsStorage(fields.contact),
                        validation_rules: _validation.createFieldsValidationRules(fields.contact),
                        validation_messages: _validation.createFieldsValidationMessages(fields.contact),
                    },
                },
                map_address_search: null,
                map_address_search_tmp: null,
                count: _config.get("shop.cart_count"),
            };
        },
        computed: {
            navigation() {
                if (this.navigation_items.length > 0) {
                    return true;
                }
                return false;
            },
            navigation_mode() {
                // return "sm";
                if (this.navigation_items.length == 1) {
                    return "lg";
                }
                return "r";
            }
        },
        methods: {
            authentication(payload) {
                if (payload.is_logged_in !== undefined && payload.is_logged_in === true) {
                    this.is_logged_in = true;
                }
            },
            onSwiper(swiper) {
                this.swiper = swiper;
            },
            onThumbsSwiper(swiper) {
                this.thumbs_swiper = swiper;
            },
            setTitleAlt($event) {
                this.title_alternative = $event;
            },
            addTitleAlt() {
                if (this.title_alternative != null) {
                    this.titles.push({ value: this.title_alternative, text: this.title_alternative });
                }
            },
            mapAdressSearch() {
                if (this.map_address_search_tmp) {
                    this.map_address_search = this.map_address_search_tmp;
                }
            },
            getCountries() {
                return _storage.get(_config.get("api.countries.index"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);
                        this.countries = response.countries;
                    }
                });
            },
            getIsLoggedIn() {
                _storage.get(_config.get("api.login"), (response) => {
                    if (_storage.isSuccess(response)) {
                        let data = _storage.getResponseData(response);
                        if (typeof(data.is_logged_in) !== "undefined") {
                            this.is_logged_in = data.is_logged_in;
                        }
                    }
                });
            },
            hideMessage() {
                this.is_success_message_shown = false;
                this.is_error_message_shown = false;
            },
            successMessage(event) {
                this.success_message = event.message;
                this.is_success_message_shown = true;
            },
            errorMessage(event) {
                this.error_message = event.message;
                this.is_error_message_shown = true;
            },
            updateCart() {
                _storage.get(_config.get("api.cart.index"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        this.count = response.product_count;
                    }
                });
            },
            updateCartCount(event) {
                if (event !== undefined && event.product_count !== undefined) {
                    this.count = event.product_count;
                }
            },
            setActiveTab(tab_name) {
                this.active_tab = tab_name;
            },
            closeNavigation() {
                this.closeOverlays();
            },
            toggleNavigation() {
                if (this.slider_trigger == false) {
                    this.slider_trigger = true;
                } else {
                    this.closeOverlays();
                }
            },
            closeOverlays() {
                if (typeof(this.$refs.navigation) !== "undefined") {
                    this.$refs.navigation.navigateReset();
                }
                this.slider_trigger = false;
            },
            checkConsent() {
                let consent = cookieStorage.getItem("cookie_consent");
                if (consent == null) {
                    this.show_cookie_consent = true;
                } else {
                    var consent_types = [];
                    consent.split(",").forEach(type => {
                        let split = type.split(":");
                        return consent_types[split[0]] = split[1] === "true" ? true : false;
                    });

                    gtag("consent", "update", {
                        ad_storage: consent_types["targeting"] ? "granted" : "denied",
                        analytics_storage: consent_types["performance"] ? "granted" : "denied",
                        functionality_storage: consent_types["functionality"] ? "granted" : "denied",
                        personalization_storage: consent_types["functionality"] ? "granted" : "denied",
                        security_storage: "granted",
                    });

                    this.cookiePreferences.allowAds = consent_types["targeting"];
                    this.cookiePreferences.allowAnalytics = consent_types["performance"];
                    this.cookiePreferences.allowPersonalization = consent_types["functionality"];
                    this.cookiePreferences.allowPreferences = consent_types["functionality"];

                    this.show_cookie_consent = false;
                }
            },
            scrollToHash() {
                const hash = window.location.hash;
                if (hash) {
                    const targetElement = document.querySelector(hash);
                    if (targetElement) {
                        this.$nextTick(() => {
                            targetElement.scrollIntoView({ behavior: "smooth" });
                        });
                    }
                }
            },
        },
        created() {
            this.is_loading = true;
            this.getCountries();

            if (_config.get("app.account.enabled")) {
                this.getIsLoggedIn();
            }

            if (_config.get("shop.enabled")) {
                // this.updateCart();
            }

            this.is_loading = false;
        },
        mounted() {
            this.checkConsent();

            document.addEventListener("click", this.closeOverlays);

            // Remove config data from DOM
            let _configParamsScript = document.querySelector("#_configParams");
            if (_configParamsScript) {
                _configParamsScript.remove();
                window._configParams = null;
            }

            if (document.readyState === "loading") {
                document.addEventListener("DOMContentLoaded", this.scrollToHash);
            } else {
                this.scrollToHash();
            }

            const header_document__navigation_strip = document.querySelector('.document__header.document__header--fixed.document__header--transparent .document__navigation_strip');
            const document__content_bg_image = document.querySelector('.document__content > .bg-image:first-child, .document__content > [section_type="hero"]');

            if (header_document__navigation_strip && document__content_bg_image) {
                header_document__navigation_strip.classList.add('scrolled_to_top');
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach((entry) => {
                        if (entry.isIntersecting) {
                            header_document__navigation_strip.classList.add('scrolled_to_top');
                        } else {
                            header_document__navigation_strip.classList.remove('scrolled_to_top');
                        }
                    });
                },
                {
                    root: null,
                    threshold: 0,
                    rootMargin: '20px 0px 0px 0px'
                });

                const dummyElement = document.createElement('div');
                document.body.prepend(dummyElement);
                observer.observe(dummyElement);
            }
        }
    });

    vm.config.compilerOptions.isCustomElement = (tag) => ['svg:style'].includes(tag);

    vm.mount(".document__wrapper");
});