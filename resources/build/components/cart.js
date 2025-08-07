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
    discount: {
        discount_code: {},
    },
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

let _cart = {
    name: "c-cart",
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
        cart_instance: {
            type: String,
            required: false
        },
        can_edit: {
            type: Boolean,
            default: true,
            required: false
        },
        enforce_minimum_quantities: {
            type: Boolean,
            default: false,
            required: false
        },
        ref_name: {
            type: String,
            default: "cart"
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
            is_discount_success_message_shown: false,
            discount_success_message: "",
            is_discount_error_message_shown: false,
            discount_error_message: "",
            saved_carts: [],
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
                items: {},
                product_items: {},
                product_item_minimum_quantities: {},
            },
            customisations: {},
            pricing: _pricing.pricing,
            discounts: _pricing.discounts,
            forms: {
                discount: {
                    ref: null,
                    field_values: _validation.createFieldsData(fields.discount),
                    field_storage: _validation.createFieldsStorage(fields.discount),
                    validation_rules: _validation.createFieldsValidationRules(fields.discount),
                    validation_messages: _validation.createFieldsValidationMessages(fields.discount),
                },
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
        getEndpoint(endpoint) {
            return endpoint + (this.cart_instance !== undefined ? "/" + this.cart_instance : "");
        },
        formatPrice(price) {
            return `£${parseFloat(price).toFixed(2)}`;
        },
        async updateCartItem(item, size) {
            let data = {
                multi_select: (item.attributes !== undefined && item.attributes.multi_select !== undefined && item.attributes.multi_select === true) ? true : false,
                size: size !== undefined ? size : null,
                qty: size !== undefined ? parseInt(size.qty) : parseInt(item.qty),
                price: null,
                custom_price: null,
            };

            if (item.custom_price && item.custom_price > 0) {
                data.custom_price = parseFloat(item.custom_price);
            } else {
                data.price = parseFloat(this.updatePricePerUnit(item));
            }

            this.clearMessages();
            const endpoint = this.getEndpoint(_config.get("api.cart.update") + `/${item.rowId}`);
            _storage.put(endpoint, (_response) => {
                const message = _storage.getResponseMessage(_response);
                if (_storage.isSuccess(_response)) {
                    this.showSuccessMessage(message);

                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);
                } else {
                    this.showErrorMessage(message);

                    this.updateCart();
                }
            }, {
                data: data
            });
        },
        async deleteCartItem(item) {
            this.clearMessages();
            const endpoint = this.getEndpoint(_config.get("api.cart.remove") + `/${item.rowId}`);
            _storage.delete(endpoint, (_response) => {
                const message = _storage.getResponseMessage(_response);
                if (_storage.isSuccess(_response)) {
                    this.showSuccessMessage(message);

                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);
                } else {
                    this.showErrorMessage(message);

                    this.updateCart();
                }
            });
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
            });

            if (response.discount_code !== undefined && response.discount_code !== '') {
                this.forms.discount.field_values.discount_code = response.discount_code;
            }

            this.$emit("update:cart", this);
        },
        groupedProductMinimumQuantityValidation(value) {
            if (this.enforce_minimum_quantities) {
                return value.qty >= value.minimum_qty;
            }
            return true;
        },
        transferGroupedProductMinimumQuantities(response) {
            if (response.product_count > 0) {
                Object.keys(response.grouped.product_items).forEach((grouped_product_item_rowId) => {
                    this.grouped.product_item_minimum_quantities[grouped_product_item_rowId] = response.grouped.product_items[grouped_product_item_rowId].qty;
                });
            }
        },
        updateCart() {
            this.is_loading = true;
            const endpoint = this.getEndpoint(_config.get("api.cart.index"));
            _storage.get(endpoint, (_response) => {
                if (_storage.isSuccess(_response)) {
                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);
                    if (this.cart_fetched == false) {
                        this.transferGroupedProductMinimumQuantities(response);
                    }
                }
                this.cart_fetched = true;
                this.is_loading = false;
            });
        },
        clearCart() {
            this.clearMessages();
            _storage.delete(this.getEndpoint(_config.get("api.cart.destroy")), (_response) => {
                if (_storage.isSuccess(_response)) {
                    const response = _storage.getResponseData(_response);
                    this.transferCartResponseToComponent(response);
                } else {
                    this.updateCart();
                }
            });
        },
        checkout() {
            this.clearMessages();
            _storage.post(this.getEndpoint(_config.get("api.checkout.can")), (_response) => {
                const message = _storage.getResponseMessage(_response);
                if (_storage.isSuccess(_response)) {
                    window.location.href = _config.get("shop.checkout.index");
                } else {
                    this.showErrorMessage(message);
                }
            });
        },
        quote_checkout(quote_id) {
            this.clearMessages();
            if (quote_id) {
                _storage.post(this.getEndpoint(_config.get("api.checkout.can")), (_response) => {
                    const message = _storage.getResponseMessage(_response);
                    if (_storage.isSuccess(_response)) {
                        window.location.href = _config.get("base") + '/quote/' + quote_id + '/checkout';
                    } else {
                        this.showErrorMessage(message);
                    }
                });
            }
        },
        computeQuantity(product) {
            if (product.product.is_multi_select) {
                return product.options.size.reduce((acc, size) => acc + parseInt(size.qty), 0);
            } else {
                return product.qty;
            }
        },
        calculatePricePerUnit(item) {
            if (item.product && item.product.configuration !== undefined && item.product.configuration.is_customisable !== undefined && item.product.configuration.is_customisable == true) {
                let
                    price = Math.max(item.price, this.pricing.minCostPerGarment),
                    discount_band = this.computeDiscountBand(item),
                    discounted_price = price * (discount_band ? discount_band.multiplier : 1);
                return discounted_price;
            } else {
                return item.price;
            }
        },
        updatePricePerUnit(item) {
            let price_per_unit = this.calculatePricePerUnit(item);
            if (price_per_unit != item.price) {
                return price_per_unit;
            } else {
                return item.price;
            }
        },
        computeDiscountBand(item) {
            let discount = false;
            this.discounts.forEach((discountBand) => {
                if (item.qty >= discountBand.minQuantity && item.qty <= discountBand.maxQuantity) {
                    discount = discountBand;
                }
            });
            return discount;
        },
        setDiscountRef(ref) {
            this.forms.discount.ref = ref;
        },
        cancelDiscountCode() {
            this.clearDiscountMessages();
            _storage.delete(this.getEndpoint(_config.get("api.discount.cancel")), (_response) => {
                this.forms.discount.field_values.discount_code = "";
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
            if (this.forms.discount.field_values.discount_code != "" && this.forms.discount.field_values.discount_code != null) {
                _storage.post(this.getEndpoint(_config.get("api.discount.apply")), (_response) => {
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
                        discount_code: this.forms.discount.field_values.discount_code
                    }
                });
            }
        },
        setCartSaveRef(ref) {
            this.forms.cart_save.ref = ref;
        },
        openCartSave() {
            this.forms.cart_save.show_modal = true;
        },
        closeCartSave() {
            this.forms.cart_save.show_modal = false;
        },
        saveCart() {
            if (this.forms.cart_save.ref) {
                this.forms.cart_save.ref.v$.$touch();
            }

            if (this.forms.cart_save.ref.v$.$invalid) {
                return;
            }

            _storage.post(this.getEndpoint(_config.get("api.cart.store_to_db")), (_response) => {
                this.closeCartSave();
                if (_storage.isSuccess(_response)) {
                    this.forms.cart_save.field_values.name = "";
                    if (this.forms.cart_save.ref) {
                        this.forms.cart_save.ref.v$.$reset();
                    }
                    this.showSuccessMessage(_storage.getResponseMessage(_response));
                    this.updateCart();
                    this.fetchCarts();
                }
                if (_storage.isError(_response)) {
                    this.showErrorMessage(_storage.getResponseMessage(_response));
                }
            }, {
                data: {
                    name: this.forms.cart_save.field_values.name
                }
            });
        },
        restoreCart(identifier) {
            _storage.get(this.getEndpoint(_config.get("api.cart.restore_from_db")), (_response) => {
                if (_storage.isSuccess(_response)) {
                    this.showSuccessMessage(_storage.getResponseMessage(_response));
                    this.updateCart();
                    this.fetchCarts();
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
        can_checkout() {
            if (this.can_edit === true && this.enforce_minimum_quantities === true && this.v$.$invalid === true) {
                return false;
            }
            return true;
        }
    },
    created() {
        this.updateCart();
        this.fetchCarts();

        _pricing.initialiseDiscounts();
    },
    template: `
    <slot
    :can_edit="can_edit"
    :enforce_minimum_quantities="enforce_minimum_quantities"

    :hide_pricing="hide_pricing"
    :is_loading="is_loading"
    :is_logged_in="is_logged_in"

    :is_success_message_shown="is_success_message_shown"
    :success_message="success_message"
    :is_error_message_shown="is_error_message_shown"
    :error_message="error_message"
    :is_discount_success_message_shown="is_discount_success_message_shown"
    :discount_success_message="discount_success_message"
    :is_discount_error_message_shown="is_discount_error_message_shown"
    :discount_error_message="discount_error_message"

    :cart_fetched="cart_fetched"
    :saved_carts="saved_carts"
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
    :customisations="customisations"

    :ref_name="ref_name"
    :forms="forms"

    :setDiscountRef="setDiscountRef"
    :cancelDiscountCode="cancelDiscountCode"
    :applyDiscountCode="applyDiscountCode"
    :formatPrice="formatPrice"
    :updateCart="updateCart"
    :updateCartItem="updateCartItem"
    :deleteCartItem="deleteCartItem"
    :clearCart="clearCart"
    :setCartSaveRef="setCartSaveRef"
    :saveCart="saveCart"
    :restoreCart="restoreCart"
    :groupedProductMinimumQuantityValidation="groupedProductMinimumQuantityValidation"
    :can_checkout="can_checkout"
    :checkout="checkout"
    :quote_checkout="quote_checkout"

    :openCartSave="openCartSave"
    :closeCartSave="closeCartSave"
    ></slot>
    `
};

export default _cart;