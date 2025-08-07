import _config from "@/utilities/config.js";
_config.init();

import cloneDeep from "lodash/cloneDeep";
import orderBy from "lodash/orderBy";

import { _storage } from "@/utilities/api.js";

import _validation from "@/utilities/validation.js";
import _product_utilities from "@/utilities/products.js";

let _product_mixin = {
    props: {
        config: {
            type: Object,
            default() {
                return {};
            }
        },
        base_customisations: {
            type: Object,
            default: null,
            required: false
        }
    },
    emits: [
        "message:error",
        "message:hide",
        "message:success",
        "update:cart",
    ],
    data() {
        return {
            hide_pricing: _config.get("shop.hide_pricing"),
            is_loading: false,
            is_success_message_shown: false,
            success_message: "",
            is_error_message_shown: false,
            error_message: "",
            qty: 1,
            variations: {},
            variation_tiers: {},
            attributes: {},
            options: {},
            configurations: [],
            image_modal_src: null,
            is_image_modal_shown: false,
            is_modal_shown: false,
            is_ready: false,
            swiper: null,
            active_variation: null,
            custom_price: null,
            colours_array_limit: 4,
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
        formatPrice(price) {
            return new Intl.NumberFormat("en-GB", { style: "currency", currency: "GBP" }).format(price);
        },
        clearProductOptions() {
            if (!this.multi_select && this.config.configuration && this.config.configuration.is_customisable !== undefined && this.config.configuration.is_customisable == false) {
                for (const [option_key, option_value] of Object.entries(this.options)) {
                    this.options[option_key] = typeof (option_value) == 'object' ? [] : null;
                    this.qty = 1;
                }
            }

            if (this.multi_select && this.options.selected !== undefined && this.options.selected.length > 0) {
                this.options.selected.forEach((selected_item) => {
                    if (selected_item.size !== undefined && selected_item.size.length > 0) {
                        selected_item.size.forEach((selected_item_size) => {
                            selected_item_size.qty = 0;
                        });
                    }
                });
            }

            for (const [variation_name, variation_value] of Object.entries(this.variations)) {
                variation_value.variations.forEach((variation) => {
                    variation.disabled = false;
                });
            }
        },
        evaluateVariationAvailability(new_val, old_val, name) {
            if (new_val != old_val) {
                if (this.variation_tiers[name] !== undefined && this.variation_tiers[name][new_val] !== undefined) {
                    for (const [variation_name, variation_variations] of Object.entries(this.variation_tiers[name][new_val])) {
                        if (this.variations[variation_name] !== undefined) {
                            this.variations[variation_name].variations.forEach((variation) => {
                                variation.disabled = !variation_variations.includes(variation.value);
                            });
                        }
                    }
                }
            }
        },
        variationInputType(type) {
            return ["color", "colour"].includes(type) ? (this.multi_select ? "select:colors:multi" : "select:colors") : "select:swatches";
        },
        extractVariationTypes(product) {
            if (product.products !== undefined && product.products.length > 0) {
                this.configurations = [];
                product.products.forEach((product_inner, product_inner_idx) => {
                    if (product_inner.attributes !== undefined && product_inner.attributes.length > 0) {
                        let product_inner_swatchable_attributes = product_inner.attributes.filter((attribute) => {
                            if (attribute.attribute !== undefined && attribute.attribute && attribute.attribute.configuration !== undefined && attribute.attribute.configuration.is_swatch !== undefined && attribute.attribute.configuration.is_swatch === true) {
                                return true;
                            }
                            return false;
                        });

                        if (product_inner_swatchable_attributes !== undefined && product_inner_swatchable_attributes.length > 0) {
                            product_inner_swatchable_attributes.forEach((attribute, attribute_idx) => {
                                let variation = {
                                    disabled: false,
                                    value: attribute.value,
                                    label: attribute.value_label,
                                    configuration: attribute.configuration !== undefined ? attribute.configuration : {},
                                    ids: [product_inner.id]
                                };

                                if (product_inner_idx === 0) {
                                    if (!this.multi_select) {
                                        this.options[attribute.name] = null;
                                        this.$watch(`options.${attribute.name}`, (new_val, old_val) => {
                                            this.evaluateVariationAvailability(new_val, old_val, attribute.name);
                                        });
                                    } else {
                                        this.options.selected = [];
                                    }

                                    this.variations[attribute.name] = {
                                        name: attribute.name,
                                        label: attribute.name_label,
                                        variations: [variation]
                                    };
                                } else {
                                    let existingVariation = this.variations[attribute.name].variations.find(_variation =>
                                        _variation.value == variation.value
                                    );

                                    if (existingVariation === undefined) {
                                        this.variations[attribute.name].variations.push(variation);
                                    } else {
                                        existingVariation.ids.push(product_inner.id);
                                    }
                                }

                                if (attribute_idx === 0) {
                                    this.variation_tiers[attribute.name] = this.variation_tiers[attribute.name] || {};
                                    this.variation_tiers[attribute.name][attribute.value] = this.variation_tiers[attribute.name][attribute.value] || {};

                                    if (this.multi_select) {
                                        let optionExists = this.configurations.some(_option => _option[attribute.name] == attribute.value);

                                        if (!optionExists) {
                                            let option = {
                                                label: attribute.value_label,
                                                priority: product_inner_idx,
                                                qty: 0,
                                                price: product_inner.price,
                                                rowId: null,
                                            };
                                            option[attribute.name] = attribute.value;

                                            if (attribute.configuration.color !== undefined) {
                                                option.hex = attribute.configuration.color;
                                            }

                                            this.configurations.push(option);
                                        }
                                    }
                                } else {
                                    let top_att = product_inner_swatchable_attributes[0];
                                    this.variation_tiers[top_att.name][top_att.value][attribute.name] = this.variation_tiers[top_att.name][top_att.value][attribute.name] || [];
                                    if (!this.variation_tiers[top_att.name][top_att.value][attribute.name].includes(attribute.value) && product_inner.is_enabled === true) {
                                        this.variation_tiers[top_att.name][top_att.value][attribute.name].push(attribute.value);
                                    }

                                    let existingOption = this.configurations.find(_option => _option[top_att.name] == top_att.value);

                                    if (existingOption) {
                                        existingOption[attribute.name] = existingOption[attribute.name] || [];
                                        let optionExists = existingOption[attribute.name].some(_option => _option.value == attribute.value);
                                        if (!optionExists) {
                                            existingOption[attribute.name].push({
                                                id: product_inner.id,
                                                is_enabled: product_inner.is_enabled,
                                                qty: 0,
                                                price: product_inner.price,
                                                value: attribute.value,
                                                value_label: attribute.value_label,
                                                rowId: null,
                                            });
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }

            if (product.attributes !== undefined && product.attributes.length > 0) {
                product.attributes.forEach((attribute) => {
                    const isSwatchable = attribute.attribute !== undefined && attribute.attribute && attribute.attribute.configuration !== undefined && attribute.attribute.configuration.is_swatch !== undefined && attribute.attribute.configuration.is_swatch === true;
                    if (isSwatchable) {
                        if (!this.multi_select) {
                            if (this.options[attribute.name] === undefined) {
                                this.options[attribute.name] = null;
                            }
                        }
                    } else {
                        if (this.attributes[attribute.name] === undefined) {
                            this.attributes[attribute.name] = attribute.value;
                        }
                    }
                });
            }
        },
        prepareCartItemPayload() {
            let payload = [];
            let options = cloneDeep(this.options);
            let attributes = cloneDeep(this.attributes);

            if (this.multi_select) {
                if (options.selected !== undefined && options.selected.length > 0) {
                    options.selected.forEach((selected_item) => {
                        if (selected_item.size !== undefined && selected_item.size.length > 0) {
                            selected_item.size.forEach((selected_item_size) => {
                                if (selected_item_size.qty > 0) {
                                    let selected_item_size_options = cloneDeep(this.options);
                                    if (this.production_process) {
                                        selected_item_size_options.production_process = this.production_process;
                                    }
                                    delete selected_item_size_options.selected;

                                    selected_item_size_options[this.primary] = selected_item[this.primary];

                                    payload.push({
                                        rowId: selected_item_size.rowId,
                                        id: selected_item_size.id,
                                        qty: selected_item_size.qty,
                                        name: this.config.name_label,
                                        price: selected_item_size.price,
                                        custom_price: this.custom_price,
                                        weight: this.weight,
                                        options: selected_item_size_options,
                                        attributes: attributes,
                                        variation_keys: {
                                            primary: this.primary,
                                            secondary: this.secondary
                                        },
                                    });
                                }
                            });
                        }
                    });
                }
            } else {
                if (this.production_process) {
                    options.production_process = this.production_process;
                }

                payload.push({
                    rowId: this.rowId,
                    id: this.id,
                    qty: this.qty,
                    name: this.config.name_label,
                    price: this.price,
                    custom_price: this.custom_price,
                    weight: this.weight,
                    options: options,
                    attributes: attributes,
                    variation_keys: {
                        primary: this.primary,
                        secondary: this.secondary
                    },
                });
            }

            return payload;
        },
        addCartItem() {
            if (this.is_disabled) {
                return;
            }
            this.clearMessages();
            this.is_loading = true;

            let cart_item_payload = this.prepareCartItemPayload();

            _storage.post(_config.get("api.cart.store"), (_response) => {
                let message = _storage.getResponseMessage(_response);
                if (_storage.isSuccess(_response)) {
                    this.showSuccessMessage(message);
                    this.$emit("update:cart");
                }

                if (_storage.isError(_response)) {
                    this.showErrorMessage(message);
                }

                if (this.is_modal_shown) {
                    this.is_modal_shown = false;
                    setTimeout(() => {
                        this.clearProductOptions();
                        this.is_loading = false;
                    }, 350);
                } else {
                    this.clearProductOptions();
                    this.is_loading = false;
                }
            }, {
                data: {
                    items: cart_item_payload,
                }
            });
        },
        openImageModal(image) {
            if (image !== undefined) {
                this.image_modal_src = image;
            }
            this.is_image_modal_shown = true;
        },
        closeImageModal() {
            this.is_image_modal_shown = false;
            setTimeout(() => {
                this.image_modal_src = null;
            }, 400);
        },
        openModal() {
            this.is_modal_shown = true;
        },
        closeModal() {
            this.is_modal_shown = false;
        },
        updateQty(qty) {
            this.qty = qty;
        },
        getQuantity(size) {
            var tmp_options = {
                colour: this.options.colour,
                size: size
            };
            let id = _product_utilities.fetchTieredProperty(this.config, tmp_options, "id");
            return id ? this.options.quantities[id] : false;
        },
        getOptionQuantity(option) {
            return option[this.secondary].reduce((acc, size) => acc + parseInt(size.qty), 0);
        },
        onSwiper(swiper) {
            this.swiper = swiper;
        },
        setActiveVariation(variation) {
            this.active_variation = variation;
        },
        setSelected(selected) {
            this.configurations.forEach(config => {
                let add = false;
                config[this.secondary].forEach(opt => {
                    if (selected[opt.id] !== undefined) {
                        opt.qty = selected[opt.id].qty;
                        opt.rowId = selected[opt.id].rowId;
                        if (opt.qty != 0) add = true;
                    }
                });
                if (add) this.options.selected.push(config);
            });
        },
        removeOption(option) {
            if (this.options.selected.length > 1) {
                const idx = this.options.selected.indexOf(option);
                if (idx !== -1) {
                    let j = this.selected_sorted.length - 1;
                    while (this.selected_sorted[j] == option && j > 0) {
                        j--;
                    }
                    this.setActiveVariation(this.selected_sorted[j][this.primary]);
                    this.options.selected.splice(idx, 1);
                }
            }
        },
        setCustomPrice(value) {
            this.custom_price = value == "" ? null : parseFloat(value);
        },
        getProductTypeAttribute() {
            return this.attributes.product_type || null;
        },
        checkForProperty(product, property) {
            if (product[property] !== undefined) {
                return product[property];
            }
            if (product.configuration !== undefined && product.configuration[property] !== undefined) {
                return product.configuration[property];
            }
            return null;
        },
    },
    computed: {
        multi_select() {
            return this.config && this.config.configuration !== undefined && this.config.configuration.is_multi_select !== undefined && this.config.configuration.is_multi_select === true ? true : false;
        },
        id() {
            let property = _product_utilities.fetchTieredProperty(this.config, this.options, "id");
            return (property !== undefined && property !== null) ? property : this.config.id;
        },
        sku() {
            let property = _product_utilities.fetchTieredProperty(this.config, this.options, "sku");
            return (property !== undefined && property !== null) ? property : this.config.sku;
        },
        surcharge() {
            let surcharge = null;

            if (this.config.attributes.length > 0) {
                surcharge = 0;
                let surcharges = [];
                this.config.attributes.forEach((attribute) => {
                    if (
                        attribute.configuration !== undefined
                        && attribute.configuration
                        && attribute.configuration.price !== undefined
                        && attribute.configuration.price !== ''
                        && attribute.configuration.price !== 0
                        && this.options[attribute.name] !== undefined
                        && this.options[attribute.name] !== null
                        && this.options[attribute.name] !== ''
                    ) {
                        surcharges.push(attribute.configuration.price);
                    }
                });
                if (surcharges.length > 0) {
                    surcharge = Math.max(...surcharges);
                }
            }
            return surcharge;
        },
        price() {
            let _price = _product_utilities.fetchTieredProperty(this.config, this.options, "price");
            let price = (_price !== undefined && _price !== null) ? _price : this.config.price;
            if (this.surcharge) {
                price = price + this.surcharge;
            }

            return price;
        },
        display_price() {
            if (this.custom_price == null) {
                let _display_price = _product_utilities.fetchTieredProperty(this.config, this.options, "display_price");
                let display_price = (_display_price !== undefined && _display_price !== null) ? _display_price : this.config.display_price;
                if (this.surcharge) {
                    display_price = this.price * ((this.tax_percentage / 100) + 1);
                }
                return display_price;
            } else {
                return this.custom_price;
            }
        },
        tax() {
            let property = _product_utilities.fetchTieredProperty(this.config, this.options, "tax");
            return (property !== undefined && property !== null) ? property : this.config.tax;
        },
        tax_percentage() {
            let property = _product_utilities.fetchTieredProperty(this.config, this.options, "tax_percentage");
            return (property !== undefined && property !== null) ? property : this.config.tax_percentage;
        },
        weight() {
            let property = _product_utilities.fetchTieredProperty(this.config, this.options, "weight");
            return (property !== undefined && property !== null) ? property : this.config.weight;
        },
        images() {
            if (this.multi_select && this.active_variation != null) {
                let images = {};
                if (this.options.selected.length == 0) {
                    let property = _product_utilities.fetchTieredProperty(this.config, { colour: this.active_variation }, "images");
                    images[this.active_variation] = property;
                } else {
                    this.options.selected.forEach(option => {
                        let property = _product_utilities.fetchTieredProperty(this.config, { colour: option.colour }, "images");
                        images[option.colour] = property;
                    });
                }
                return images;
            } else {
                let property = _product_utilities.fetchTieredProperty(this.config, this.options, "images");
                return (property !== undefined && property !== null) ? property : this.config.images;
            }
        },
        image() {
            if (this.images && this.images.length > 0) {
                return this.images[0];
            }
            return null;
        },
        active_colour() {
            if (this.multi_select && this.active_variation != null) {
                let option = this.options.selected.find(_option => _option.colour == this.active_variation);
                return option != null ? option.hex : null;
            } else {
                let property = _product_utilities.fetchTieredProperty(this.config, this.options, "attributes");
                for (const attribute of property) {
                    if (attribute.name == this.primary) return attribute.configuration.color ?? "#000000";
                }
            }
            return "#000000";
        },
        active_colour_label() {
            if (this.multi_select && this.active_variation != null) {
                let option = this.options.selected.find(_option => _option.colour == this.active_variation);
                return option != null ? option.label : null;
            } else {
                let property = _product_utilities.fetchTieredProperty(this.config, this.options, "attributes");
                for (const attribute of property) {
                    if (attribute.name == this.primary) return attribute.value_label ?? null;
                }
            }
            return null;
        },
        colours_array() {
            let color_array = [];
            if (
                this.variations !== undefined
                && this.variations.colour !== undefined
                && this.variations.colour.variations !== undefined
                && this.variations.colour.variations.length > 0
            ) {
                this.variations.colour.variations.forEach((variation) => {
                    if (
                        variation.configuration !== undefined
                        && variation.configuration.color !== undefined
                    ) {
                        color_array.push(variation.configuration.color);
                    }
                });
            }
            return color_array;
        },
        is_disabled() {
            return this.is_loading || this.v$.$invalid || this.v$.$error;
        },
        primary() {
            return Object.keys(this.variations)[0];
        },
        secondary() {
            return Object.keys(this.variations)[1];
        },
        selected_sorted() {
            return orderBy(this.options.selected, "priority");
        },
        totalQuantity() {
            return this.multi_select ? this.options.selected.reduce((acc, option) => acc + this.getOptionQuantity(option), 0) : this.qty;
        },
        additional_validations() {}
    },
    validations() {
        let validations = {
            rules: {},
            messages: {}
        };
        var option_rules = {};
        var base_rules = {};

        if (this.multi_select) {
            option_rules.selected = {
                validations: {
                    rules: {
                        required: _validation.rules.required,
                        minLength: _validation.rules.minLength(1)
                    },
                    messages: {
                        minLength: "Please select at least one " + this.primary
                    }
                }
            }
        } else {
            if (this.primary !== undefined) {
                option_rules[this.primary] = {
                    validations: {
                        rules: {
                            required: _validation.rules.required
                        }
                    }
                }
            }

            if (this.config.attributes !== undefined && this.config.attributes.length > 0) {
                this.config.attributes.forEach((attribute) => {
                    if (attribute.configuration !== undefined && attribute.configuration !== null) {
                        if (attribute.configuration.required !== undefined && attribute.configuration.required === true) {
                            option_rules[attribute.name] = {
                                validations: {
                                    rules: {
                                        required: _validation.rules.required
                                    }
                                }
                            }
                        }
                    }
                });
            }

            base_rules.qty = {
                validations: {
                    rules: {
                        minValue: _validation.rules.minValue(1)
                    }
                }
            }
        }

        validations.rules.base = _validation.createFieldsValidationRules(base_rules);
        validations.rules.options = _validation.createFieldsValidationRules(option_rules);
        validations.messages.options = _validation.createFieldsValidationMessages(option_rules);

        if (this.additional_validations != null) {
            Object.keys(this.additional_validations).forEach(key => {
                var _rules = _validation.createFieldsValidationRules(this.additional_validations[key]);
                var _messages = _validation.createFieldsValidationMessages(this.additional_validations[key]);

                validations.rules[key] = validations.rules[key] === undefined ? _rules : {
                    ...validations.rules[key],
                    ..._rules
                }
                validations.messages[key] = validations.messages[key] === undefined ? _messages : {
                    ...validations.messages[key],
                    ..._messages
                }
            });
        }

        let rules = {
            options: validations.rules.options
        };
        this.validation_messages = validations.messages;
        if (validations.rules.base != null) {
            Object.assign(rules, validations.rules.base);
        }
        return rules;
    },
    watch: {
        config: {
            handler(new_val, old_val) {
                if (old_val === undefined || old_val.id === undefined || (old_val.id !== undefined && new_val.id !== undefined && old_val.id !== new_val.id)) {
                    let self = this;
                    this.extractVariationTypes(new_val);
                    if (this.multi_select && this.config.selected) {
                        this.setSelected(this.config.selected);
                    }
                    this.is_ready = true;
                }
            },
            deep: true,
            immediate: true
        }
    },
    created() {
        if (this.multi_select) {
            this.$watch(`options.selected`, async function(new_val, old_val) {
                    if (old_val === undefined) {
                        await this.$nextTick();
                        old_val = [];
                    }
                    if (new_val !== undefined) {
                        if (new_val.length > old_val.length) {
                            let new_option = new_val.filter(el => !old_val.includes(el))[0];
                            if (new_option && new_option[this.primary] !== undefined) {
                                this.active_variation = new_option[this.primary];
                            }
                        } else {
                            this.active_variation = new_val.length != 0 ? new_val[new_val.length - 1][this.primary] : this.active_variation;
                        }
                    }
                },
                {
                    immediate: true
                }
            );
        } else {
            this.$watch(`options.${this.primary}`,  async function(new_val, old_val) {
                this.active_variation = new_val;
            },
            {
                immediate: true
            });
        }
    },
}

export default _product_mixin;