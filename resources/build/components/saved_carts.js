import _config from "@/utilities/config.js";
_config.init();

import { useVuelidate } from "@vuelidate/core";

import _utilities from "@/utilities/utilities.js";
import _product_utilities from "@/utilities/products.js";
import { _storage } from "@/utilities/api.js";

import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

import _pricing from "@/utilities/pricing.js";

let fields = {
    cart_save: {
        name: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        }
    }
};

let _saved_carts = {
    name: "c-saved_carts",
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    emits: [
        "message:hide",
        "message:success",
        "message:error",
        "update:cart",
    ],
    props: {
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
            saved_carts: [],
            cart_fetched: false,
            forms: {
                cart_save: {
                    ref: null,
                    field_values: _validation.createFieldsData(fields.cart_save),
                    field_storage: _validation.createFieldsStorage(fields.cart_save),
                    validation_rules: _validation.createFieldsValidationRules(fields.cart_save),
                    validation_messages: _validation.createFieldsValidationMessages(fields.cart_save),
                    show_modal: false,
                }
            }
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
        getEndpoint(endpoint) {
            return endpoint + (this.cart_instance !== undefined ? "/" + this.cart_instance : "");
        },
        formatPrice(price) {
            return `£${parseFloat(price).toFixed(2)}`;
        },
        updateCart() {
            this.$emit('update:cart');
        },
        restoreCart(identifier) {
            _storage.get(this.getEndpoint(_config.get("api.cart.restore_from_db")), (_response) => {
                if (_storage.isSuccess(_response)) {
                    this.showSuccessMessage(_storage.getResponseMessage(_response));
                    this.updateCart();
                    this.fetchCarts();
                    if (_config.get('shop.cart.index')) {
                        window.location.href = _config.get('shop.cart.index');
                    }
                }
                if (_storage.isError(_response)) {
                    this.showErrorMessage(_storage.getResponseMessage(_response));
                }
            }, {
                params: {
                    identifier: identifier
                }
            });
        },
        fetchCarts() {
            this.saved_carts = [];
            if (this.is_logged_in) {
                _storage.get(this.getEndpoint(_config.get("api.cart.fetch_saved_carts")), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        if (response.carts.length > 0) {
                            this.saved_carts = response.carts;
                        }
                    }
                    if (_storage.isError(_response)) {
                    }
                });
            }
        }
    },
    computed: {
    },
    created() {
        this.fetchCarts();
    },
    template: `
    <slot

    :hide_pricing="hide_pricing"
    :is_loading="is_loading"
    :is_logged_in="is_logged_in"

    :is_success_message_shown="is_success_message_shown"
    :success_message="success_message"
    :is_error_message_shown="is_error_message_shown"
    :error_message="error_message"

    :cart_fetched="cart_fetched"
    :saved_carts="saved_carts"

    :forms="forms"

    :restoreCart="restoreCart"
    ></slot>
    `
};

export default _saved_carts;