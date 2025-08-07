import _config from "@/utilities/config.js";
_config.init();

import _utilities from "@/utilities/utilities.js";
import { _api, _storage } from "@/utilities/api.js";
import _validation from "@/utilities/validation.js";

let fields = {
    first_name: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
        storage: true
    },
    last_name: {
        validations: {
            rules: {
                required: _validation.rules.required
            }
        },
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
    customer_id: {
        default: null,
    }
};

let _cards = {
    name: "c-cards",
    data() {
        return {
            countries: [],
            fields,
            ref: "checkout",
            action: "checkout",
            field_values: _validation.createFieldsData(fields),
            field_storage: _validation.createFieldsStorage(fields),
            validation_messages: _validation.createFieldsValidationMessages(fields),
        };
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
    },
    computed: {
        full_name() {
            const { first_name, last_name } = this.field_values;
            return [first_name, last_name].filter(Boolean).join(" ");
        },
        cards_validation() {
            const rules = {
                first_name: fields.first_name,
                last_name: fields.last_name,
                billing_address_line_1: fields.billing_address_line_1,
                billing_address_line_2: fields.billing_address_line_2,
                billing_city: fields.billing_city,
                billing_county: fields.billing_county,
                billing_postcode: fields.billing_postcode,
                billing_country: fields.billing_country,
                customer_id: fields.customer_id,
            };

            return _validation.createFieldsValidationRules(rules);
        },
    },
    created() {
        this.getCountries();
    },
    template: `
    <div class="cards">
        <slot
        :countries="countries"
        :fields="fields"
        :ref="ref"
        :action="action"
        :field_values="field_values"
        :field_storage="field_storage"
        :validation_rules="cards_validation"
        :validation_messages="validation_messages"
        :full_name="full_name"
        ></slot>
    </div>
    `
};

export default _cards;