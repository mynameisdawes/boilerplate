import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })
import { useVuelidate } from "@vuelidate/core";

import { default as c_range_slider } from "../range_slider.js";
import { default as c_rotation_slider } from "../rotation_slider.js";
import { default as c_message } from "../message.js";


let _orientation = {
    name: "c-orientation",
    mixins: [_option_mixin],
    components: {
        "c-range_slider": c_range_slider,
        "c-rotation_slider": c_rotation_slider,
        "c-message": c_message
    },
    setup () {
        const v$ = useVuelidate({$registerAs: "orientation", $autoDirty: true});
        return { v$ };
    },
    data() {
        return {
        }
    },
    validations() {
        let validations = {
            validate: {
                validations: {
                    rules: {
                        valid:  _validation.helpers.withMessage("Your upload is out of bounds, please adjust its size or position", function(value) {
                            return !value;
                        })
                    }
                }
            }
        };

        return _validation.createFieldsValidationRules(validations);
    },
    template: `
    <div>
        <div v-for="(data, value) in product.options.customisations">
            <div v-if="data.selected && data.file.file_path != null">
                <h3 class="font-normal">
                    <a class="no-underline block">
                        Print Size <span class="text-sm capitalize">({{ data.label }} {{ product.customisations[value].positions[data.position].label }})</span>
                    </a>
                </h3>
                <div class="grid grid-cols-12:3">
                    <div class="col-span-12:1t2e col-span-8:3 relative pt-10 flex items-center">
                        <div class="absolute h-4/5 mx-auto text-center w-full top-0:1t2e">
                            <span class="text-sm text-light">W {{ Math.round(data.dimensions.w) }}mm</span>
                            <span class="text-sm text-light"> | </span>
                            <span class="text-sm text-light">H {{ Math.round(data.dimensions.h) }}mm</span>
                        </div>
                        <div class="mx-auto w-80:3 w-full">
                            <c-range_slider
                            :ref="'slider_' + value"
                            v-model="data.inputHeightVal"
                            :side="value"
                            @update:model-value="product.setSlide(value)"
                            @update_previews="product.generatePreviews(value)"
                            ></c-range_slider>
                        </div>
                    </div>
                    <div class="col-span-12:1t2e col-span-4:3 relative pt-10 flex items-center mt-7:1t2e">
                        <div class="absolute top-0 w-full">
                            <div class="text-sm text-light text-center">ROTATE ({{ data.rotation }}&#176;)</div>
                        </div>
                        <c-rotation_slider
                        v-model="data.rotation"
                        @update:model-value="product.setSlide(value)"
                        @update_previews="product.generatePreviews(value)"
                        ></c-rotation_slider>
                    </div>
                </div>
                <c-message content="Your upload may be too small for the print size you have selected" class="message--warning py-2" :trigger="product.options.customisations[value].uploadFeedback.level > 1"></c-message>
            </div>
        </div>
    </div>
    `
}

export default _orientation;
