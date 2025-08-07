import _config from "@/utilities/config.js";
_config.init();

import _utilities from "@/utilities/utilities.js";
import { _api, _storage } from "@/utilities/api.js";

import _product_utilities from "@/utilities/products.js";

import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

import debounce from "lodash/debounce";

let fields = {
    can_checkout: {
        validations: {
            rules: {
                required: _validation_helpers.withParams(
                    { type: "required" },
                    function(value) {
                        return value;
                    }
                )
            }
        },
        default: false
    },
    first_name: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        default: _config.get("shop.first_name")
    },
    last_name: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        default: _config.get("shop.last_name")
    },
    email: {
        validations: {
            rules: {
                required: _validation.rules.required,
                email: _validation.rules.email
            }
        },
        default: _config.get("shop.email")
    },
    phone: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
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
                    function(value) {
                        if (
                            typeof(this.field_values) !== "undefined"
                            && typeof(this.field_values.password) !== "undefined"
                        ) {
                            if (this.field_values.password == "") {
                                return true;
                            }
                            if (this.field_values.password == value) {
                                return true;
                            }
                            return false;

                        } else {
                            return true;
                        }
                    }
                )
            },
            messages: {
                sameAs: "This confirmation password must match"
            }
        }
    },
    shipping_type: {
        default: null
    },
    shipping_address_line_1: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    shipping_address_line_2: {
        storage: true
    },
    shipping_city: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    shipping_county: {
        storage: true
    },
    shipping_postcode: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    shipping_country: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    shipping_address_id: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        default: null,
    },
    manual_shipping_address: {
        default: true,
    },
    save_shipping_address: {
        default: false,
    },
    same_as_shipping: {
        default: true,
        storage: true
    },
    billing_address_line_1: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    billing_address_line_2: {
        storage: true
    },
    billing_city: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    billing_county: {
        storage: true
    },
    billing_postcode: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    billing_country: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    billing_address_id: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        default: null,
    },
    manual_billing_address: {
        default: true,
    },
    save_billing_address: {
        default: false,
    },
    addresses_save: {
        default: false,
        storage: true
    },
    shipping_method: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    notes: {
    },
    can_edit: {
        default: true,
        storage: true
    },
    enforce_minimum_quantities: {
        default: true,
        storage: true
    },
    low_res_artwork_provided: {
        default: false,
        storage: true
    },
    agree_terms: {
        validations: {
            rules: {
                required: _validation_helpers.withParams(
                    { type: "required" },
                    function(value) {
                        return value;
                    }
                )
            },
            messages: {
                required: "Please agree to the terms & conditions to proceed"
            }
        },
        default: false,
        storage: true
    },
    agree_marketing: {
        default: false,
        storage: true
    },
    discount_code: {
    },
    amount: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        default: "0.00"
    },
    stripe_customer_id: {
        default: _config.get("payments.stripe.stripe_customer_id")
    }
};

if (_config.get("shop.email_domain_check.enabled")) {

    if (typeof(fields.email.validations.rules) === "undefined") {
        fields.email.validations.rules = {};
    }

    fields.email.validations.rules.matchesDomain = (value) => {
        let is_valid = true;
        const regex = new RegExp("@(" + _config.get("shop.email_domain_check.list").replaceAll(".", "\\.") + ")$", "gi");
        if (value != "") { return regex.test(value); }
        return is_valid;
    };

    if (typeof(fields.email.validations.messages) === "undefined") {
        fields.email.validations.messages = {};
    }

    fields.email.validations.messages.matchesDomain = "Please enter an email with an approved domain";
}

if (_config.get("shop.customer_unique")) {

    if (typeof(fields.email.validations.rules) === "undefined") {
        fields.email.validations.rules = {};
    }

    fields.email.validations.rules.isUnique = debounce((value) => {
        if (value == "") { return true; }
        return _storage.post(_config.get("api.exists"), (_response) => {
            return !_storage.isSuccess(_response);
        }, {
            data: {
                email: value
            }
        });
    }, 200);

    if (typeof(fields.email.validations.messages) === "undefined") {
        fields.email.validations.messages = {};
    }

    fields.email.validations.messages.isUnique = "This email has already been used to place an order. Please enter another one";
}

let _checkout = {
    name: "c-checkout",
    emits: [
        "authentication",
        "message:hide",
        "message:success",
        "message:error",
    ],
    props: {
        prop_quote_only: {
            type: Boolean,
            default: false
        },
        cart_instance: {
            type: String,
            required: false
        },
        from_model: {
            type: String,
            required: false
        },
        email_match_auth: {
            type: Boolean,
            default: false
        },
        ref_name: {
            type: String,
            default: "checkout"
        }
    },
    data() {
        return {
            hide_pricing: _config.get("shop.hide_pricing"),
            is_logged_in: _config.get("user.is_logged_in"),
            is_loading: false,
            is_success_message_shown: false,
            success_message: "",
            is_error_message_shown: false,
            error_message: "",
            is_auth_success_message_shown: false,
            auth_success_message: "",
            is_auth_error_message_shown: false,
            auth_error_message: "",
            is_discount_success_message_shown: false,
            discount_success_message: "",
            is_discount_error_message_shown: false,
            discount_error_message: "",
            cart_fetched: false,
            items: {},
            count: null,
            subtotal: null,
            product_items: {},
            product_count: null,
            product_subtotal: null,
            shipping_items: {},
            shipping_count: null,
            shipping_subtotal: null,
            discount: null,
            tax: null,
            total: null,
            formatted: {
                subtotal: null,
                product_subtotal: null,
                shipping_subtotal: null,
                discount: null,
                tax: null,
                total: null,
            },
            grouped: {
                product_items: {},
            },
            use_user_addresses: _config.get("shop.use_user_addresses"),
            shipping_address_ref: null,
            billing_address_ref: null,
            shipping_required: _config.get("shop.shipping_required"),
            billing_required: _config.get("shop.billing_required"),
            agree_terms: _config.get("shop.agree_terms"),
            agree_marketing: _config.get("shop.agree_marketing"),
            exists: null,
            countries: [],
            same_as_shipping: false,
            shipping_methods: [],
            action: "checkout",
            field_values: _validation.createFieldsData(fields),
            field_storage: _validation.createFieldsStorage(fields),
            validation_messages: _validation.createFieldsValidationMessages(fields),
            options: {
                quote_only: this.prop_quote_only
            },
            verify_user: false,
            user_verified: false,
        };
    },
    watch: {
        "product_subtotal": {
            handler(new_val, old_val) {
                if (old_val !== null && new_val != old_val) {
                    this.debouncedGetShippingMethods();
                }
            }
        },
        "field_values": {
            handler() {
                this.debouncedGetShippingMethods();
            }, deep: true
        },
        "shipping_required": {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    if (new_val === false) {
                        this.field_values.shipping_address_line_1 = "";
                        this.field_values.shipping_address_line_2 = "";
                        this.field_values.shipping_city = "";
                        this.field_values.shipping_county = "";
                        this.field_values.shipping_postcode = "";
                        this.field_values.shipping_country = "";

                        this.field_values.same_as_shipping = false;
                    }
                }
            }, immediate: true
        },
        "billing_required": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (new_val === true && this.field_values.same_as_shipping == true) {
                        this.field_values.billing_address_line_1 = this.field_values.shipping_address_line_1;
                        this.field_values.billing_address_line_2 = this.field_values.shipping_address_line_2;
                        this.field_values.billing_city = this.field_values.shipping_city;
                        this.field_values.billing_county = this.field_values.shipping_county;
                        this.field_values.billing_postcode = this.field_values.shipping_postcode;
                        this.field_values.billing_country = this.field_values.shipping_country;
                    } else {
                        this.field_values.billing_address_line_1 = "";
                        this.field_values.billing_address_line_2 = "";
                        this.field_values.billing_city = "";
                        this.field_values.billing_county = "";
                        this.field_values.billing_postcode = "";
                        this.field_values.billing_country = "";

                        this.field_values.same_as_shipping = false;
                    }
                }
            }, immediate: true
        },
        "field_values.email": {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    if (this.email_match_auth) {
                        this.checkUserExistence(new_val, "api.matches");
                        this.verify_user = this.exists === true;
                    } else {
                        this.checkUserExistence(new_val, "api.exists");
                    }
                }
            }
        },
        "field_values.same_as_shipping": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (new_val === true && this.billing_required == true) {
                        this.field_values.billing_address_line_1 = this.field_values.shipping_address_line_1;
                        this.field_values.billing_address_line_2 = this.field_values.shipping_address_line_2;
                        this.field_values.billing_city = this.field_values.shipping_city;
                        this.field_values.billing_county = this.field_values.shipping_county;
                        this.field_values.billing_postcode = this.field_values.shipping_postcode;
                        this.field_values.billing_country = this.field_values.shipping_country;
                        if (this.is_logged_in && this.use_user_addresses && !this.field_values.manual_shipping_address) {
                            this.field_values.billing_address_id = this.field_values.shipping_address_id;
                        }
                    } else {
                        this.field_values.billing_address_line_1 = "";
                        this.field_values.billing_address_line_2 = "";
                        this.field_values.billing_city = "";
                        this.field_values.billing_county = "";
                        this.field_values.billing_postcode = "";
                        this.field_values.billing_country = "";
                        if (this.is_logged_in && this.use_user_addresses && !this.field_values.manual_shipping_address) {
                            this.field_values.billing_address_id = "";
                        }
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_address_line_1": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_address_line_1 = new_val;
                    } else {
                        this.field_values.billing_address_line_1 = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_address_line_2": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_address_line_2 = new_val;
                    } else {
                        this.field_values.billing_address_line_2 = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_city": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_city = new_val;
                    } else {
                        this.field_values.billing_city = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_county": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_county = new_val;
                    } else {
                        this.field_values.billing_county = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_postcode": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_postcode = new_val;
                    } else {
                        this.field_values.billing_postcode = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_country": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.field_values.billing_country = new_val;
                    } else {
                        this.field_values.billing_country = "";
                    }
                }
            }, immediate: true
        },
        "field_values.shipping_address_id": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (this.field_values.same_as_shipping == true && this.billing_required == true && this.is_logged_in && this.use_user_addresses && !this.field_values.manual_shipping_address) {
                        this.field_values.billing_address_id = new_val;
                    }
                }
            }, immediate: true
        }
    },
    methods: {
        clearMessages() {
            this.is_success_message_shown = false;
            this.is_error_message_shown = false;
            this.$emit("message:hide");
        },
        showSuccessMessage(message) {
            this.success_message = message;
            this.is_success_message_shown = true;
            this.$emit("message:success", { message: this.success_message });
        },
        showErrorMessage(message) {
            this.error_message = message;
            this.is_error_message_shown = true;
            this.$emit("message:error", { message: this.error_message });
        },
        clearAuthMessages() {
            this.is_auth_success_message_shown = false;
            this.is_auth_error_message_shown = false;
        },
        showAuthSuccessMessage(message) {
            this.auth_success_message = message;
            this.is_auth_success_message_shown = true;
        },
        showAuthErrorMessage(message) {
            this.auth_error_message = message;
            this.is_auth_error_message_shown = true;
        },
        clearDiscountMessages() {
            this.is_discount_success_message_shown = false;
            this.is_discount_error_message_shown = false;
        },
        showDiscountSuccessMessage(message) {
            this.discount_success_message = message;
            this.is_discount_success_message_shown = true;
        },
        showDiscountErrorMessage(message) {
            this.discount_error_message = message;
            this.is_discount_error_message_shown = true;
        },
        checkCanCheckout() {
            this.clearMessages();
            _storage.post(this.getEndpoint(_config.get("api.checkout.can")), (_response) => {
                const message = _storage.getResponseMessage(_response);
                if (_storage.isSuccess(_response)) {
                    this.field_values.can_checkout = true;
                } else {
                    this.field_values.can_checkout = false;
                    this.showErrorMessage(message);
                }
            });
        },
        debouncedGetShippingMethods: debounce(function() {
            this.getShippingMethods();
        }, 300),
        getShippingMethods() {
            return _storage.post(_config.get("api.shipping_methods.index"), (_response) => {
                this.checkCanCheckout();
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.shipping_methods = response.shipping_methods;

                    if (this.field_values.shipping_method == "") {
                        if (this.shipping_count > 0) {
                            Object.keys(this.shipping_items).forEach((shipping_item) => {
                                if (
                                    this.shipping_items[shipping_item].options
                                    && this.shipping_items[shipping_item].options.method_code
                                    && this.shipping_items[shipping_item].options.method_code === 'custom'
                                ) {
                                    this.field_values.shipping_method = "custom";
                                } else {
                                    this.shipping_methods.forEach((shipping_method) => {
                                        if (
                                            this.shipping_items[shipping_item].options
                                            && this.shipping_items[shipping_item].options.method_code
                                            && this.shipping_items[shipping_item].options.method_code === shipping_method.code
                                            && this.shipping_items[shipping_item].price === shipping_method.price
                                        ) {
                                            this.manageCartShipping(shipping_method);
                                        }
                                    });
                                }
                            });
                        }
                    } else {
                        this.shipping_methods.forEach((shipping_method) => {
                            if (this.field_values.shipping_method == shipping_method.code) {
                                if (this.shipping_count > 0) {
                                    Object.keys(this.shipping_items).forEach((shipping_item) => {
                                        if (
                                            this.shipping_items[shipping_item].options
                                            && this.shipping_items[shipping_item].options.method_code
                                            && this.shipping_items[shipping_item].options.method_code == shipping_method.code
                                            && this.shipping_items[shipping_item].price !== shipping_method.price
                                        ) {
                                            this.manageCartShipping(shipping_method);
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            }, {
                data: {
                    ...this.field_values,
                }
            });
        },
        getCountries() {
            return _storage.get(_config.get("api.countries.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.countries = response.countries;
                    if (this.countries.length == 1) {
                        this.field_values.shipping_country = this.countries[0].value;
                    }
                }
            });
        },
        formatOptions(options) {
            let value = [];
            for (const [option_key, option_value] of Object.entries(options)) {
                if (option_key != "parent_id") {
                    if (option_key == "size") {
                        value.push("<strong>" + _utilities.ucfirst(option_key) + ":</strong>&nbsp;" + option_value.toUpperCase());
                    } else {
                        value.push("<strong>" + _utilities.ucfirst(option_key) + ":</strong>&nbsp;" + _utilities.ucfirst(option_value));
                    }
                }
            }
            return value.join(" | ");
        },
        formatPrice(price) {
            return "£" + parseFloat(price).toFixed(2);
        },
        changeShippingType(shipping_type) {
            this.same_as_shipping = this.field_values.same_as_shipping;
            if (shipping_type.code == 'collection') {
                this.field_values.same_as_shipping = false;
            } else {
                this.field_values.same_as_shipping = this.same_as_shipping;
            }
            this.field_values.shipping_type = shipping_type.code;
        },
        changeShippingMethod(form, shipping_method) {
            if (this.field_values.shipping_method == shipping_method.code) {
                this.field_values.shipping_method = "";
                if (typeof(form.validation_rules.shipping_method) !== "undefined") {
                    form.validation_rules.shipping_method.$touch();
                }
                this.manageCartShipping(null);
                return;
            }
            if (!shipping_method.is_disabled) {
                this.field_values.shipping_method = shipping_method.code;
                if (typeof(form.validation_rules.shipping_method) !== "undefined") {
                    form.validation_rules.shipping_method.$touch();
                }
                this.manageCartShipping(shipping_method);
                return;
            }
        },
        manageCartShipping(shipping_method) {
            if (shipping_method != null) {
                _storage.post(this.getEndpoint(_config.get("api.cart.store")), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        this.transferCartResponseToComponent(response);

                        if (this.shipping_count > 0) {
                            Object.keys(this.shipping_items).forEach((shipping_item) => {
                                if (this.shipping_items[shipping_item].options.method_code !== undefined) {
                                    this.field_values.shipping_method = this.shipping_items[shipping_item].options.method_code;
                                }
                            });
                        }

                        this.$emit("update:cart");
                    }
                }, {
                    data: {
                        items: [
                            {
                                id: shipping_method.code,
                                type: "shipping",
                                name: shipping_method.name,
                                qty: 1,
                                price: shipping_method.price,
                                weight: 0,
                                options: {
                                    method_name: shipping_method.name,
                                    method_code: shipping_method.code,
                                    shipping_provider_id: (typeof(shipping_method.configuration) !== "undefined" && typeof(shipping_method.configuration.onecrm_shipping_provider_id) !== "undefined") ? shipping_method.configuration.onecrm_shipping_provider_id : null,
                                    shipping_country: this.field_values.shipping_country
                                }
                            }
                        ]
                    }
                });
            } else {
                _storage.delete(this.getEndpoint(_config.get("api.cart.remove") + "/shipping"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        this.transferCartResponseToComponent(response);

                        this.field_values.shipping_method = "";

                        this.$emit("update:cart");
                    }
                }, {
                    data: {
                        type: "shipping"
                    }
                });
            }
        },
        transferCartResponseToComponent(response) {
            Object.keys(response).forEach(response_property => {
                if (this[response_property] !== undefined && !['formatted', 'grouped'].includes(response_property)) {
                    this[response_property] = response[response_property];
                }
                if (this[response_property] !== undefined && ['formatted', 'grouped'].includes(response_property)) {
                    Object.keys(response[response_property]).forEach(nested_response_property => {
                        if (this[response_property][nested_response_property] !== undefined) {
                            this[response_property][nested_response_property] = response[response_property][nested_response_property];
                        }
                    });
                }

                if (response.discount_code !== undefined && response.discount_code !== '') {
                    this.field_values.discount_code = response.discount_code;
                }
            });
        },
        updateCart() {
            this.is_loading = true;
            _storage.get(this.getEndpoint(_config.get("api.cart.index")), (_response) => {
                if (_storage.isSuccess(_response)) {
                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);
                }
                this.cart_fetched = true;
                this.is_loading = false;
            });
        },
        getEndpoint(endpoint) {
            return endpoint + (this.cart_instance != undefined ? "/" + this.cart_instance : "") + '?customisations_exclude_preview=true';
        },
        checkUserExistence: debounce(function(email, endpoint) {
            if (email != "") {
                _storage.post(_config.get(endpoint), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);
                        this.exists = response.exists;
                        this.verify_user = response.exists === true;
                    }
                }, {
                    data: {
                        email: email
                    }
                });
            } else {
                this.exists = null;
            }
        }, 300),
        attemptUserLogin() {
            this.clearAuthMessages();
            if (this.field_values.email != "" && this.field_values.password != "") {
                this.is_loading = true;
                _storage.post(_config.get("api.login"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);

                        this.$emit('authentication', {
                            is_logged_in: true
                        });

                        if (response.first_name !== undefined) {
                            _config.set("shop.first_name", response.first_name);
                            this.field_values.first_name = response.first_name;
                        }

                        if (response.last_name !== undefined) {
                            _config.set("shop.last_name", response.last_name);
                            this.field_values.last_name = response.last_name;
                        }

                        if (response.email !== undefined) {
                            _config.set("shop.email", response.email);
                            this.field_values.email = response.email;
                        }

                        if (response.stripe_id !== undefined) {
                            _config.set("payments.stripe.stripe_customer_id", response.stripe_id);
                            this.field_values.stripe_customer_id = response.stripe_id;
                        }

                        this.showAuthSuccessMessage(_storage.getResponseMessage(_response));

                        this.is_loading = false;

                        setTimeout(() => {
                            this.is_logged_in = true;
                        }, 1000);
                    }
                    if (_storage.isError(_response)) {
                        this.showAuthErrorMessage(_storage.getResponseMessage(_response));
                    }
                }, {
                    data: {
                        email: this.field_values.email,
                        password: this.field_values.password
                    }
                });
            }
        },
        verifyUserPassword() {
            if (this.field_values.email != "" && this.field_values.password != "") {
                _storage.post(_config.get("api.verify"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        setTimeout(() => {
                            this.exists = null;
                            this.user_verified = true;
                        }, 1000);
                    }
                }, {
                    data: {
                        email: this.field_values.email,
                        password: this.field_values.password
                    }
                });
            }
        },
        submitUserPassword(form) {
            form.validation_rules.email.$touch();
            form.validation_rules.password.$touch();

            if (form.validation_rules.email.$invalid == false && form.validation_rules.password.$invalid == false) {
                this.attemptUserLogin();
                if (this.email_match_auth) {
                    this.verifyUserPassword();
                }
            }
        },
        cancelDiscountCode() {
            this.clearDiscountMessages();
            _storage.delete(_config.get("api.discount.cancel"), (_response) => {
                this.field_values.discount_code = "";
                if (_storage.isSuccess(_response)) {
                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);

                    this.showDiscountSuccessMessage(_storage.getResponseMessage(_response));
                }
                if (_storage.isError(_response)) {
                    this.showDiscountErrorMessage(_storage.getResponseMessage(_response));
                }
            });
        },
        applyDiscountCode() {
            this.clearDiscountMessages();
            if (this.field_values.discount_code != "") {
                _storage.post(_config.get("api.discount.apply"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        this.transferCartResponseToComponent(response);

                        this.showDiscountSuccessMessage(_storage.getResponseMessage(_response));
                    }
                    if (_storage.isError(_response)) {
                        this.showDiscountErrorMessage(_storage.getResponseMessage(_response));
                    }
                }, {
                    data: {
                        discount_code: this.field_values.discount_code
                    }
                });
            }
        },
        checkForUserData(name) {
            const stored_value = window.localStorage.getItem(`${name}--fields`);
            if (stored_value) {
                let storage_data = JSON.parse(stored_value);
                if (storage_data.email != undefined && storage_data.email != "") {
                    return;
                } else {
                    this.is_loading = true;
                    _storage.post(_config.get("api.checkout_quote.show") + "/" + this.cart_instance, (_response) => {
                        if (_storage.isSuccess(_response)) {
                            let response = _storage.getResponseData(_response);
                            const quote = response.quote;
                            this.field_values.first_name = quote.first_name;
                            this.field_values.last_name = quote.last_name;
                            this.field_values.email = quote.email;
                            this.field_values.phone = quote.phone;
                        }
                        this.is_loading = false;
                    });
                }
            }
        },
        setShippingAddressId(id) {
            this.field_values.shipping_address_id = id;
        },
        setShippingAddressRef(ref) {
            this.shipping_address_ref = ref;
        },
        toggleManualShippingAddress() {
            this.field_values.manual_shipping_address = !this.field_values.manual_shipping_address;
        },
        setBillingAddressId(id) {
            this.field_values.billing_address_id = id;
        },
        setBillingAddressRef(ref) {
            this.billing_address_ref = ref;
        },
        toggleManualBillingAddress() {
            this.field_values.manual_billing_address = !this.field_values.manual_billing_address;
        },
    },
    computed: {
        full_name() {
            return [
                this.field_values.first_name,
                this.field_values.last_name,
            ].filter(Boolean).join(" ");
        },
        checkout_validation() {
            let rules = {};

            rules.can_checkout = fields.can_checkout;

            rules.first_name = fields.first_name;
            rules.last_name = fields.last_name;
            rules.email = fields.email;
            rules.phone = fields.phone;

            if (this.is_logged_in == false) {
                if (this.exists === true) {
                    rules.password = fields.password;
                }

                if (this.exists === false) {
                    rules.password = fields.password;
                    rules.password_confirmation = fields.password_confirmation;
                }
            }

            if (!this.options.quote_only) {
                if (
                    this.shipping_required === true
                    && (this.field_values.shipping_type === null || (this.field_values.shipping_type !== null && this.field_values.shipping_type === 'delivery'))
                ) {
                    if (this.is_logged_in == false || this.use_user_addresses == false || this.field_values.manual_shipping_address == true) {
                        rules.shipping_address_line_1 = fields.shipping_address_line_1;
                        rules.shipping_address_line_2 = fields.shipping_address_line_2;
                        rules.shipping_city = fields.shipping_city;
                        rules.shipping_county = fields.shipping_county;
                        rules.shipping_postcode = fields.shipping_postcode;
                        rules.shipping_country = fields.shipping_country;
                        rules.same_as_shipping = fields.same_as_shipping;
                    } else {
                        rules.shipping_address_id = fields.shipping_address_id;
                    }
                }

                if ((this.shipping_required === true && this.field_values.same_as_shipping === false && this.billing_required === true) || this.shipping_required === false) {
                    if (this.is_logged_in == false || this.use_user_addresses == false || this.field_values.manual_billing_address == true) {
                        rules.billing_address_line_1 = fields.billing_address_line_1;
                        rules.billing_address_line_2 = fields.billing_address_line_2;
                        rules.billing_city = fields.billing_city;
                        rules.billing_county = fields.billing_county;
                        rules.billing_postcode = fields.billing_postcode;
                        rules.billing_country = fields.billing_country;
                    } else {
                        rules.billing_address_id = fields.billing_address_id;
                    }
                }

                if (this.shipping_methods.length > 0) {
                    rules.shipping_method = fields.shipping_method;
                }

                if (this.agree_terms === true) {
                    rules.agree_terms = fields.agree_terms;
                }
            } else {
                rules.notes = fields.notes;
                rules.low_res_artwork_provided = fields.low_res_artwork_provided;
            }

            rules.amount = fields.amount;
            rules.stripe_customer_id = fields.stripe_customer_id;

            return _validation.createFieldsValidationRules(rules);
        },
        shipping_types() {
            let shipping_types = [];
            if (this.shipping_methods.length > 0) {
                this.shipping_methods.forEach((shipping_method) => {
                    if (shipping_method.is_disabled === false && shipping_method.is_hidden === false) {
                        let shipping_type_found = shipping_types.some((shipping_type) => {
                            return shipping_type.code === shipping_method.type;
                        });

                        if (shipping_type_found == false) {
                            shipping_types.push({
                                code: shipping_method.type,
                                name: shipping_method.formatted.type,
                            });
                        }
                    }
                });
            }

            if (this.field_values.shipping_type === null && shipping_types.length > 0) {
                let delivery_shipping_type_found = shipping_types.some((shipping_type) => {
                    return shipping_type.code === 'delivery';
                });

                if (shipping_types.length > 1 && delivery_shipping_type_found) {
                    this.field_values.shipping_type = 'delivery';
                } else {
                    this.field_values.shipping_type = shipping_types[0].code;
                }
            }

            if (shipping_types.length > 0) {
                shipping_types.sort((a, b) => {
                    if (a.code < b.code) { return 1; }
                    if (a.code > b.code) { return -1; }
                    return 0;
                });
            }

            return shipping_types;
        },
        available_shipping_methods() {
            let available_shipping_methods = this.shipping_methods.filter((shipping_method) => {
                let matching_shipping_method_type = true;
                if (this.field_values.shipping_type && this.field_values.shipping_type !== shipping_method.type) {
                    matching_shipping_method_type = false;
                }
                if (shipping_method.is_disabled === true || shipping_method.is_hidden === true || matching_shipping_method_type === false) {
                    if (this.field_values.shipping_method == shipping_method.code) {
                        this.field_values.shipping_method = "";
                        this.manageCartShipping(null);
                    }
                }
                if (shipping_method.is_hidden === true || matching_shipping_method_type === false) {
                    return false;
                }
                return true;
            });

            if (available_shipping_methods.length == 1 && this.field_values.shipping_method == "") {
                this.manageCartShipping(available_shipping_methods[0]);
            }

            return available_shipping_methods;
        },
    },
    created() {
        if (this.from_model != null && this.cart_instance != null) {
            this.field_values.from_model = this.from_model;
            this.field_values.from_id = this.cart_instance;
        }

        this.getCountries().then(() => {
            this.getShippingMethods();
        });

        this.updateCart();
        // this.checkCanCheckout();
    },
    template: `
    <div class="checkout">
        <slot
        :hide_pricing="hide_pricing"
        :is_loading="is_loading"

        :is_success_message_shown="is_success_message_shown"
        :success_message="success_message"
        :is_error_message_shown="is_error_message_shown"
        :error_message="error_message"
        :is_auth_success_message_shown="is_auth_success_message_shown"
        :auth_success_message="auth_success_message"
        :is_auth_error_message_shown="is_auth_error_message_shown"
        :auth_error_message="auth_error_message"
        :is_discount_success_message_shown="is_discount_success_message_shown"
        :discount_success_message="discount_success_message"
        :is_discount_error_message_shown="is_discount_error_message_shown"
        :discount_error_message="discount_error_message"

        :cart_fetched="cart_fetched"

        :items="items"
        :count="count"
        :subtotal="subtotal"
        :product_items="product_items"
        :product_count="product_count"
        :product_subtotal="product_subtotal"
        :shipping_items="shipping_items"
        :shipping_count="shipping_count"
        :shipping_subtotal="shipping_subtotal"
        :discount="discount"
        :tax="tax"
        :total="total"
        :formatted="formatted"
        :grouped="grouped"

        :use_user_addresses="use_user_addresses"
        :shipping_address_ref="shipping_address_ref"
        :billing_address_ref="billing_address_ref"
        :shipping_required="shipping_required"
        :billing_required="billing_required"
        :agree_terms="agree_terms"
        :agree_marketing="agree_marketing"
        :exists="exists"
        :countries="countries"
        :shipping_types="shipping_types"
        :available_shipping_methods="available_shipping_methods"

        :ref_name="ref_name"
        :action="action"
        :field_values="field_values"
        :field_storage="field_storage"
        :validation_rules="checkout_validation"
        :validation_messages="validation_messages"
        :full_name="full_name"
        :formatOptions="formatOptions"
        :formatPrice="formatPrice"
        :changeShippingType="changeShippingType"
        :changeShippingMethod="changeShippingMethod"
        :options="options"
        :submitUserPassword="submitUserPassword"
        :cancelDiscountCode="cancelDiscountCode"
        :applyDiscountCode="applyDiscountCode"
        :checkForUserData="checkForUserData"
        :setShippingAddressId="setShippingAddressId"
        :setShippingAddressRef="setShippingAddressRef"
        :toggleManualShippingAddress="toggleManualShippingAddress"
        :setBillingAddressId="setBillingAddressId"
        :setBillingAddressRef="setBillingAddressRef"
        :toggleManualBillingAddress="toggleManualBillingAddress"

        :is_logged_in="is_logged_in"
        :verify_user="verify_user"
        :user_verified="user_verified"
        ></slot>
    </div>
    `
};

export default _checkout;