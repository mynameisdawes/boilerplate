import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

let fields = {
    _method: {
        default: "put"
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
    phone: {
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
    new_password: {
        validations: {
            rules: {
                minLength: _validation.rules.minLength(8)
            }
        }
    },
    new_password_confirmation: {
        validations: {
            rules: {
                requiredIf: _validation_helpers.withParams(
                    { type: "requiredIf" },
                    function(value) {
                        if (value != "") {
                            return true;
                        }
                        if (
                            typeof(this.field_values) !== "undefined"
                            && typeof(this.field_values.new_password) !== "undefined"
                        ) {
                            if (this.field_values.new_password == "") {
                                return true;
                            }
                            return false;

                        } else {
                            return true;
                        }
                    }
                ),
                sameAs: _validation_helpers.withParams(
                    { type: "sameAs" },
                    function(value) {
                        if (
                            typeof(this.field_values) !== "undefined"
                            && typeof(this.field_values.new_password) !== "undefined"
                        ) {
                            if (this.field_values.new_password == "") {
                                return true;
                            }
                            if (this.field_values.new_password == value) {
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
    password: {
        validations: {
            rules: {
                requiredIf: _validation_helpers.withParams(
                    { type: "requiredIf" },
                    function(value) {
                        if (value != "") {
                            return true;
                        }
                        if (
                            typeof(this.field_values) !== "undefined"
                            && typeof(this.field_values.new_password) !== "undefined"
                        ) {
                            if (this.field_values.new_password == "") {
                                return true;
                            }
                            return false;

                        } else {
                            return true;
                        }
                    }
                ),
                minLength: _validation.rules.minLength(8)
            },
            messages: {
                requiredIf: "Please enter your existing password"
            }
        }
    },
    shipping_address_line_1: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    shipping_address_line_2: {},
    shipping_city: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    shipping_county: {},
    shipping_postcode: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    shipping_country: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    same_as_shipping: {
        default: true
    },
    billing_address_line_1: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    billing_address_line_2: {},
    billing_city: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    billing_county: {},
    billing_postcode: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    },
    billing_country: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        }
    }
};

let _onecrm_dashboard = {
    name: "c-onecrm_dashboard",
    data() {
        return {
            shipping_required: true,
            billing_required: true,
            countries: [],
            forms: {
                personal_details: {
                    ref: "personal_details",
                    action: "api.onecrm.personal_details.update",
                    field_values: _validation.createFieldsData(fields),
                    field_storage: _validation.createFieldsStorage(fields),
                    validation_rules: _validation.createFieldsValidationRules(fields),
                    validation_messages: _validation.createFieldsValidationMessages(fields),
                },
            },
            panels: {
                personal_details: {
                    data_fetched: false,
                    is_loading: true,
                    is_expanded: false,
                    phone: null,
                    email: null,
                    shipping_address: null,
                    billing_address_line_1: "",
                    billing_address_line_2: "",
                    billing_city: "",
                    billing_county: "",
                    billing_postcode: "",
                    billing_country: "",
                    billing_address: null,
                    full_name: null,
                    tel: null,
                    stripe_customer_id: null
                }
            },
        };
    },
    watch: {
        "shipping_required": {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    if (new_val === false) {
                        this.forms.personal_details.field_values.shipping_address_line_1 = "";
                        this.forms.personal_details.field_values.shipping_address_line_2 = "";
                        this.forms.personal_details.field_values.shipping_city = "";
                        this.forms.personal_details.field_values.shipping_county = "";
                        this.forms.personal_details.field_values.shipping_postcode = "";
                        this.forms.personal_details.field_values.shipping_country = "";

                        this.forms.personal_details.field_values.same_as_shipping = false;
                    }
                }
            }, immediate: true
        },
        "billing_required": {
            handler(new_val, old_val) {
                if (new_val != old_val && this.shipping_required == true) {
                    if (new_val === true && this.forms.personal_details.field_values.same_as_shipping == true) {
                        this.forms.personal_details.field_values.billing_address_line_1 = this.forms.personal_details.field_values.shipping_address_line_1;
                        this.forms.personal_details.field_values.billing_address_line_2 = this.forms.personal_details.field_values.shipping_address_line_2;
                        this.forms.personal_details.field_values.billing_city = this.forms.personal_details.field_values.shipping_city;
                        this.forms.personal_details.field_values.billing_county = this.forms.personal_details.field_values.shipping_county;
                        this.forms.personal_details.field_values.billing_postcode = this.forms.personal_details.field_values.shipping_postcode;
                        this.forms.personal_details.field_values.billing_country = this.forms.personal_details.field_values.shipping_country;
                    } else {
                        this.forms.personal_details.field_values.billing_address_line_1 = "";
                        this.forms.personal_details.field_values.billing_address_line_2 = "";
                        this.forms.personal_details.field_values.billing_city = "";
                        this.forms.personal_details.field_values.billing_county = "";
                        this.forms.personal_details.field_values.billing_postcode = "";
                        this.forms.personal_details.field_values.billing_country = "";

                        this.forms.personal_details.field_values.same_as_shipping = false;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.same_as_shipping": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (new_val === true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_address_line_1 = this.forms.personal_details.field_values.shipping_address_line_1;
                        this.forms.personal_details.field_values.billing_address_line_2 = this.forms.personal_details.field_values.shipping_address_line_2;
                        this.forms.personal_details.field_values.billing_city = this.forms.personal_details.field_values.shipping_city;
                        this.forms.personal_details.field_values.billing_county = this.forms.personal_details.field_values.shipping_county;
                        this.forms.personal_details.field_values.billing_postcode = this.forms.personal_details.field_values.shipping_postcode;
                        this.forms.personal_details.field_values.billing_country = this.forms.personal_details.field_values.shipping_country;
                    } else {
                        this.forms.personal_details.field_values.billing_address_line_1 = this.panels.personal_details.billing_address_line_1;
                        this.forms.personal_details.field_values.billing_address_line_2 = this.panels.personal_details.billing_address_line_2;
                        this.forms.personal_details.field_values.billing_city = this.panels.personal_details.billing_city;
                        this.forms.personal_details.field_values.billing_county = this.panels.personal_details.billing_county;
                        this.forms.personal_details.field_values.billing_postcode = this.panels.personal_details.billing_postcode;
                        this.forms.personal_details.field_values.billing_country = this.panels.personal_details.billing_country;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_address_line_1": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_address_line_1 = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_address_line_1 = this.panels.personal_details.billing_address_line_1;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_address_line_2": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_address_line_2 = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_address_line_2 = this.panels.personal_details.billing_address_line_2;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_city": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_city = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_city = this.panels.personal_details.billing_city;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_county": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_county = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_county = this.panels.personal_details.billing_county;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_postcode": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_postcode = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_postcode = this.panels.personal_details.billing_postcode;
                    }
                }
            }, immediate: true
        },
        "forms.personal_details.field_values.shipping_country": {
            handler(new_val, old_val) {
                if (this.panels.personal_details.is_loading == false && new_val != old_val && this.shipping_required == true) {
                    if (this.forms.personal_details.field_values.same_as_shipping == true && this.billing_required == true) {
                        this.forms.personal_details.field_values.billing_country = new_val;
                    } else {
                        this.forms.personal_details.field_values.billing_country = this.panels.personal_details.billing_country;
                    }
                }
            }, immediate: true
        },
    },
    methods: {
        getCountries() {
            _storage.get(_config.get("api.countries.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.countries = response.countries;
                }
            });
        },
        getUserDetails() {
            let panel = this.panels.personal_details;
            _storage.get(_config.get("api.onecrm.personal_details.show"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    let field_values = this.forms.personal_details.field_values;
                    for (const [option_key, option_value] of Object.entries(field_values)) {
                        if (typeof(response[option_key]) !== "undefined") {
                            field_values[option_key] = response[option_key];
                        }
                    }

                    for (const [option_key, option_value] of Object.entries(panel)) {
                        if (typeof(response[option_key]) !== "undefined") {
                            panel[option_key] = response[option_key];
                        }
                    }

                    setTimeout(() => {
                        panel.is_loading = false;
                        panel.data_fetched = true;
                    }, 10);
                }
            });
        },
        personalDetailsSuccess() {
            this.panels.personal_details.is_expanded = false;
            this.panels.personal_details.is_loading = true;
            this.getUserDetails();
        },
        stripeCardsSuccess(_response) {
            if (_storage.isSuccess(_response)) {
                let response = _storage.getResponseData(_response);
                _storage.post(_config.get("api.payment_stripe.customer_create"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        this.panels.personal_details.stripe_customer_id = response.customer;
                    }
                }, {
                    data: {
                        stripe_customer_id: response.customer
                    }
                });
            }
        }
    },
    computed: {
        personal_details_validation() {
            let rules = {};

            rules.first_name = fields.first_name;
            rules.last_name = fields.last_name;
            rules.phone = fields.phone;
            rules.email = fields.email;
            rules.new_password = fields.new_password;
            rules.new_password_confirmation = fields.new_password_confirmation;
            rules.password = fields.password;

            if (this.shipping_required == true) {
                rules.shipping_address_line_1 = fields.shipping_address_line_1;
                rules.shipping_address_line_2 = fields.shipping_address_line_2;
                rules.shipping_city = fields.shipping_city;
                rules.shipping_county = fields.shipping_county;
                rules.shipping_postcode = fields.shipping_postcode;
                rules.shipping_country = fields.shipping_country;
                rules.same_as_shipping = fields.same_as_shipping;
            }

            if ((this.shipping_required == true && this.forms.personal_details.field_values.same_as_shipping == false && this.billing_required == true) || this.shipping_required == false) {
                rules.billing_address_line_1 = fields.billing_address_line_1;
                rules.billing_address_line_2 = fields.billing_address_line_2;
                rules.billing_city = fields.billing_city;
                rules.billing_county = fields.billing_county;
                rules.billing_postcode = fields.billing_postcode;
                rules.billing_country = fields.billing_country;
            }

            return _validation.createFieldsValidationRules(rules);
        },
    },
    mounted() {
        this.getCountries();
        this.getUserDetails();
    },
    template: `
    <slot
    :countries="countries"
    :forms="forms"
    :panels="panels"

    :personal_details_validation_rules="personal_details_validation"

    :personalDetailsSuccess="personalDetailsSuccess"
    :stripeCardsSuccess="stripeCardsSuccess"
    ></slot>
    `
};

export default _onecrm_dashboard;