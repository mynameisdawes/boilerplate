import _config from "@/utilities/config.js";
_config.init();

import { useVuelidate } from "@vuelidate/core";

import { _storage } from "@/utilities/api.js";

import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

let fields = {
    email: {
        validations: {
            rules: {
                required: _validation.rules.required,
                email: _validation.rules.email
            }
        }
    },
};

let _cart = {
    name: "c-discount_promo",
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    emits: [
        "message:hide",
        "message:success",
        "message:error",
    ],
    props: {
        discount_id: {
            type: String,
            required: true
        },
        ref_name: {
            type: String,
            default: "discount_promo"
        }
    },
    data() {
        return {
            is_loading: false,
            is_success_message_shown: false,
            success_message: "",
            is_error_message_shown: false,
            error_message: "",
            field_values: _validation.createFieldsData(fields),
            field_storage: _validation.createFieldsStorage(fields),
            validation_rules: _validation.createFieldsValidationRules(fields),
            validation_messages: _validation.createFieldsValidationMessages(fields),
        };
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
        register() {
            this.clearMessages();
            if (this.field_values.email != "") {
                this.is_loading = true;
                _storage.post(_config.get("api.discount_promo.register"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        this.showSuccessMessage(_storage.getResponseMessage(_response));
                    }
                    if (_storage.isError(_response)) {
                        this.showErrorMessage(_storage.getResponseMessage(_response));
                    }
                    this.is_loading = false;
                }, {
                    data: {
                        discount_id: this.discount_id,
                        email: this.field_values.email
                    }
                });
            }
        },
    },
    template: `
    <slot
    :is_loading="is_loading"

    :is_success_message_shown="is_success_message_shown"
    :success_message="success_message"
    :is_error_message_shown="is_error_message_shown"
    :error_message="error_message"

    :ref_name="ref_name"
    :field_values="field_values"
    :field_storage="field_storage"
    :validation_rules="validation_rules"
    :validation_messages="validation_messages"

    :register="register"
    ></slot>
    `
};

export default _cart;