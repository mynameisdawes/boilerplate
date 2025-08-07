import { ref } from 'vue';

import _product_mixin from "@/mixins/products.js";

import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; });
import { useVuelidate } from "@vuelidate/core";

import _product_utilities from "@/utilities/products.js";

import cloneDeep from "lodash/cloneDeep";

import { default as _pricing } from "@/utilities/pricing.js";

const SIDE_TEMPLATE = {
    selected: false,
    dimensions: {
    //values in mm
        w: null,
        h: null,
        x: null,
        y: null,
        area: 0,
    },

    h: null,//h refers to the distance from top to bottom of the UNROTATED UPLOAD
    inputHeightVal: 0.5,//serves as a percentage of the maxHeightVal and printsize in mm
    position: null,
    preview: null,

    printPixelValues: {
        w: null,
        h: null,
    },

    rect: {
    //these values represent the current state of the RECT of the upload at all times.
    //note that this rect is NOT in relation to the screen, but in relation to the canvas.
        w: null,
        h: null,
        x: null,
        y: null,
    //================================================================================
    },

    resize: {
    //maintains uploads x and y values as a percentage of the canvas width
    //to ensure that the image remains well placed on screen resize
    //NOTE: THIS TAKES INTO ACCOUNT OFFSETS SO IS ACTUALLY THE X AND Y VALUES FOR THE ======CENTER======= OF THE UPLOAD
        x: null,
        y: null,
    },

    rotation: 0,

    file: {},
    w: null,//w refers to the distance from left to right of the UNROTATED UPLOAD
    whratio: null,

    recWidth: 0,

    uploadFeedback: {
        level: 0,
        message: null
    }
};

let _customisable_product = {
    name: "c-customisable",
    mixins: [_product_mixin],
    props: {
        base_customisations: {
            type: Object,
            default: null,
            required: false
        }
    },
    setup () {
        const steps = ref(null);

        const v$ = useVuelidate();
        return { steps, v$ };
    },
    data() {
        return {
            customisations: {},
            panels: {
                colour_select: {
                    is_expanded: true
                }
            },
            active_step: 1,
            note_trigger: false,
            note: {
                tmp: "",
            },
            canvas_error: false,
            dpi_messages: [
                "Your artwork is high resolution meaning you'll get the best possible print result",
                "Your artwork is a good resolution meaning you should still get a great print result",
                "Your artwork resolution is suboptimal meaning you may not get the best possible print result",
                "Your artwork is low resolution meaning we'll need to work with you to get the best possible print result",
            ],
            pricing: _pricing.pricing,
            discounts: _pricing.discounts,
            validation_messages: null
        }
    },
    methods: {
        extractCustomisations(product) {
            this.options.customisations = {};
            if (product.configuration.builder_config !== undefined) {
                for (const [label, side] of Object.entries(product.configuration.builder_config)) {
                    this.customisations[label] = side;
                    this.customisations[label].image_copy = {};
                    if (this.base_customisations && this.base_customisations.designs[label] !== undefined) {
                        this.options.customisations[label] = this.base_customisations.designs[label];
                        const cust = this.base_customisations.designs[label];
                        this.$nextTick(() => {
                            this.copyImage({
                                extension: cust.file.file_extension,
                                name: cust.file.file_name,
                                path: cust.file.file_path
                            }, label);
                        })
                    } else {
                        this.options.customisations[label] = Object.assign({}, cloneDeep(SIDE_TEMPLATE), { label: side.label, position: side.default ? side.default : Object.keys(side.positions)[0] });
                    }
                    this.panels[label] = true;
                    if (Object.keys(this.options.customisations).length == 1) {
                        this.$nextTick(() => this.options.customisations[label].selected = true);
                    }
                    // this.setDpiWarning(label, 0);
                }
            }
        },
        mutateDesign(side, vals) {
            this.mergeObj(this.options.customisations[side], vals, Object.keys(vals));
        },
        mergeObj(mainObj, secondaryObj, properties = Object.keys(mainObj)) {
            properties.forEach(item => {
                Object.assign(mainObj, {
                    [item]: JSON.parse(JSON.stringify(secondaryObj[item])),
                });
            })
        },
        openNote() {
            this.note_trigger = true;
            this.note.tmp = this.options.note;
        },
        closeNote() {
            this.note_trigger = false;
            this.note.tmp = "";
        },
        saveNote() {
            this.options.note = this.note.tmp;
            this.closeNote();
        },
        copyImage(file, side) {
            this.options.customisations[side].selected = true;
            this.options.customisations[side].file = {
                file_path: file.path,
                file_name: file.name,
                file_extension: file.extension
            };
            this.customisations[side].image_copy = {
                file: file.file,
                path: file.path,
                name: file.name,
                extension: file.extension
            };
            this.setSlide(side);
        },
        clearCopyImage(side) {
            for (const [key, val] of Object.entries(this.customisations)) {
                if (key == side) {
                    val.image_copy = null;
                }
            }
        },
        onlyAllowNumbers(e) {
            // Prevents user from creating a negative number on the input fields
            let key = e.which,
                forbiddenKeys = [
                    38,//upkey
                    40,//downkey
                    189,//minuskey
                    190,//decimalpoint
                ]
            ;
            if (forbiddenKeys.indexOf(e.which) !== -1) e.preventDefault();
        },
        setSlide(side) {
            if (this.swiper && Object.keys(this.options.customisations).length > 1) {
                this.swiper.slideTo(Object.entries(this.options.customisations).findIndex(e => e[0] == side));
            }
        },
        setCanvasError(error) {
            this.canvas_error = error;
        },
        getCanvasError() {
            return this.canvas_error;
        },
        generatePreviews(side) {
            const sides = this.options.customisations;
            let c = document
                    .getElementById(`canvas-container-${side}`)
                    .getElementsByTagName(`canvas`)[0];
            sides[side].preview = c.toDataURL();
        },
        sideToggled(val, side) {
            if (val) {
                this.setSlide(side);
            }
        },
        setDpiWarning(side, val) {
            this.options.customisations[side].uploadFeedback.level = val;
            this.options.customisations[side].uploadFeedback.message = this.dpi_messages[val];
        },
        generateDesignId() {
            _storage.get(_config.get("api.cart.store"), (_response) => {
                let response = _storage.getResponseData(_response);
                let id;
                do {
                    id = Date.now().toString(36) + Math.random().toString(36).substring(2);
                } while(Object.values(response.items).filter(item => item.id == id).length > 0);
                this.options.customisation_id = id;
            });
        },
        setIsLoading(value) {
            this.is_loading = value;
        },
        getActiveStep() {
            return this.active_step;
        },
        setActiveStep(step) {
            this.active_step = step;
            this.panels.colour_select.is_expanded = step == 1;
        },
        prepareCartItemPayload() {
            let payload = [];
            let options = cloneDeep(this.options);

            if (this.multi_select) {
                if (options.selected !== undefined && options.selected.length > 0) {
                    options.selected.forEach((selected_item) => {
                        if (selected_item.size !== undefined && selected_item.size.length > 0) {
                            selected_item.size.forEach((selected_item_size) => {
                                if (selected_item_size.qty > 0) {
                                    let selected_item_size_options = cloneDeep(this.options);
                                    delete selected_item_size_options.selected;

                                    selected_item_size_options[this.primary] = selected_item[this.primary];

                                    payload.push({
                                        rowId: selected_item_size.rowId,
                                        id: selected_item_size.id,
                                        qty: selected_item_size.qty,
                                        name: this.config.name_label,
                                        price: this.price_per_unit,
                                        custom_price: this.custom_price,
                                        weight: 0,
                                        options: selected_item_size_options,
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
                payload.push({
                    rowId: this.rowId,
                    id: this.id,
                    qty: this.qty,
                    name: this.config.name_label,
                    price: this.price_per_unit,
                    custom_price: this.custom_price,
                    weight: 0,
                    options: options,
                    variation_keys: {
                        primary: this.primary,
                        secondary: this.secondary
                    },
                });
            }

            return payload;
        },
    },
    computed: {
        display_price() {
            let multiplier = 1 + (this.tax_percentage / 100);
            return this.price_per_unit * multiplier;
        },
        print_price() {
            let sides = this.options.customisations;
            let count = 0;
            if (sides) {
                return Object.keys(sides).reduce((acc, key) => {
                    const side = sides[key];
                    let side_cost = 0;
                    if (side.selected) {
                        count++;
                        let
                            print_cost = count == 1 ? this.pricing.firstPrintCost : this.pricing.secondPrintCost,
                            price_per_mm = print_cost / this.pricing.mmSquared,
                            chargeable_mm = side.dimensions.area - (side.dimensions.area % this.pricing.mmSquared);
                        side_cost = chargeable_mm * price_per_mm;
                    }
                    return acc + side_cost;
                }, 0);
            }

            return count;
        },
        price_per_unit() {
            if (this.custom_price == null) {
                if (this.has_upload) {
                    let
                        base_price = this.price + this.print_price,
                        price = Math.max(base_price, this.pricing.minCostPerGarment),
                        discounted_price = price * (this.discountBand ? this.discountBand.multiplier : 1);
                    return discounted_price;
                } else {
                    return this.price;
                }
            } else {
                return this.custom_price;
            }
        },
        builder_images() {
            let images = {};
            if (this.multi_select && this.active_variation != null) {
                if (this.options.selected.length == 0) {
                    let property = _product_utilities.fetchTieredProperty(this.config, { colour: this.active_variation }, "builder_images");
                    images[this.active_variation] = property;
                } else {
                    this.options.selected.forEach(option => {
                        let property = _product_utilities.fetchTieredProperty(this.config, { colour: option.colour }, "builder_images");
                        images[option.colour] = property;
                    });
                }
                return images;
            } else {
                let property = _product_utilities.fetchTieredProperty(this.config, this.options, "builder_images");
                return (property !== undefined && property !== null) ? property : {};
            }
        },
        is_disabled() {
            return this.v$.$invalid || this.v$.$error;
        },
        additional_validations() {
            let validations = {};

            if (!this.multi_select && this.active_step >= 3) {
                validations.options = {};
                if (this.secondary !== undefined) {
                    validations.options[this.secondary] = {
                        validations: {
                            rules: {
                                required: _validation.rules.required
                            }
                        }
                    }
                }
            }

            return validations;
        },
        discountBand() {
            let discount = false;
            this.discounts.forEach(discountBand => {
                if (this.totalQuantity >= discountBand.minQuantity && this.totalQuantity <= discountBand.maxQuantity) discount = discountBand;
            });
            return discount;
        },
        has_upload() {
            let has_upload = false;
            if (this.options.customisations) {
                Object.entries(this.options.customisations).forEach(side => {
                    if (side[1].selected && side[1].file != null && side[1].file.file_path != null) {
                        has_upload = true;
                    }
                });
            }
            return has_upload;
        },
    },
    watch: {
        config: {
            handler(new_val, old_val) {
                if (old_val === undefined || old_val.id === undefined) {
                    let self = this;
                    this.extractVariationTypes(new_val);
                    this.extractCustomisations(new_val);
                    if (this.multi_select && this.config.selected) {
                        this.setSelected(this.config.selected);
                    }
                    if (!this.multi_select) {
                        if (this.config.qty) this.qty = this.config.qty;
                        if (this.config.options) {
                            Object.keys(this.config.options).forEach(option => {
                                this.options[option] = this.config.options[option];
                            });
                        }
                    }
                    _pricing.initialiseDiscounts();
                    this.is_ready = true;
                }
            },
            deep: true,
            immediate: true
        }
    },
    mounted() {
        if (this.base_customisations !== null) {
            this.options.customisation_id = this.base_customisations.id;
            this.options.note = this.base_customisations.note;
        } else {
            this.generateDesignId();
        }
    },
    template: `
    <slot
    :config="config"
    :multi_select="multi_select"
    :hide_pricing="hide_pricing"
    :qty="qty"
    :variations="variations"
    :attributes="attributes"
    :primary="primary"
    :secondary="secondary"
    :options="options"
    :customisations="customisations"
    :success_message="success_message"
    :error_message="error_message"
    :is_success_message_shown="is_success_message_shown"
    :is_error_message_shown="is_error_message_shown"
    :image_modal_src="image_modal_src"
    :is_image_modal_shown="is_image_modal_shown"
    :is_modal_shown="is_modal_shown"
    :variationInputType="variationInputType"
    :addCartItem="addCartItem"
    :formatPrice="formatPrice"
    :colours_array="colours_array"
    :colours_array_limit="colours_array_limit"
    :openImageModal="openImageModal"
    :closeImageModal="closeImageModal"
    :openModal="openModal"
    :closeModal="closeModal"
    :openNote="openNote"
    :closeNote="closeNote"
    :saveNote="saveNote"
    :updateQty="updateQty"
    :getOptionQuantity="getOptionQuantity"
    :sku="sku"
    :display_price="display_price"
    :price_per_unit="price_per_unit"
    :price="price"
    :weight="weight"
    :builder_images="builder_images"
    :images="images"
    :image="image"
    :is_disabled="is_disabled"
    :is_ready="is_ready"
    :is_loading="is_loading"
    :setIsLoading="setIsLoading"
    :panels="panels"
    :mutateDesign="mutateDesign"
    :generatePreviews="generatePreviews"
    :swiper="swiper"
    :onSwiper="onSwiper"
    :copyImage="copyImage"
    :clearCopyImage="clearCopyImage"
    :validation_rules="v$"
    :validation_messages="validation_messages"
    :getActiveStep="getActiveStep"
    :setActiveStep="setActiveStep"
    :note_trigger="note_trigger"
    :note="note"
    :setSlide="setSlide"
    :setCanvasError="setCanvasError"
    :getCanvasError="getCanvasError"
    :sideToggled="sideToggled"
    :setDpiWarning="setDpiWarning"
    :active_variation="active_variation"
    :setActiveVariation="setActiveVariation"
    :active_colour="active_colour"
    :active_colour_label="active_colour_label"
    :configurations="configurations"
    :selected_sorted="selected_sorted"
    :removeOption="removeOption"
    :custom_price="custom_price"
    :setCustomPrice="setCustomPrice"
    ></slot>
    `
}

export default _customisable_product;