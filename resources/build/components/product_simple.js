import _product_mixin from "@/mixins/products.js";

import { useVuelidate } from "@vuelidate/core";

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; });

let _simple_product = {
    name: "c-product",
    mixins: [_product_mixin],
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    emits: [
        "update:cart",
    ],
    methods: {
        onThumbsSwiper(thumbsSwiper) {
            this.thumbsSwiper = thumbsSwiper;
            window.addEventListener("resize", () => {
                thumbsSwiper.update();
            });
        },
        setSlide(index) {
            this.swiper.slideTo(index);
        }
    },
    computed: {
        additional_validations() {
            let validations = {};

            if (!this.multi_select) {
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
        }
    },
    template: `
    <slot
    :config="config"
    :multi_select="multi_select"
    :validation_rules="v$"
    :hide_pricing="hide_pricing"
    :qty="qty"
    :variations="variations"
    :attributes="attributes"
    :active_variation="active_variation"
    :setActiveVariation="setActiveVariation"
    :primary="primary"
    :secondary="secondary"
    :configurations="configurations"
    :options="options"
    :selected_sorted="selected_sorted"
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
    :removeOption="removeOption"
    :colours_array="colours_array"
    :colours_array_limit="colours_array_limit"
    :openImageModal="openImageModal"
    :closeImageModal="closeImageModal"
    :openModal="openModal"
    :closeModal="closeModal"
    :updateQty="updateQty"
    :getOptionQuantity="getOptionQuantity"
    :sku="sku"
    :display_price="display_price"
    :price="price"
    :weight="weight"
    :images="images"
    :image="image"
    :active_colour_label="active_colour_label"
    :is_disabled="is_disabled"
    :is_ready="is_ready"
    :is_loading="is_loading"
    :onSwiper="onSwiper"
    :onThumbsSwiper="onThumbsSwiper"
    :setSlide="setSlide"
    ></slot>
    `
}

export default _simple_product;