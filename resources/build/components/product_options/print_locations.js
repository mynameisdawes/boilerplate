import _option_mixin from "@/mixins/options.js";

import _config from "@/utilities/config.js";
_config.init();

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; })
import { useVuelidate } from "@vuelidate/core";

let _print_locations = {
    name: "c-print_locations",
    mixins: [_option_mixin],
    setup () {
        const v$ = useVuelidate({$registerAs: "print_locations"});
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
                        side_chosen: _validation.helpers.withMessage("Please select at least one print location", function(value) {
                            for (const [key, val] of Object.entries(value)) {
                                if (val.selected) return true
                            };
                            return false;
                        }),
                        valid_upload: _validation.helpers.withMessage("Please check your uploaded images", function(value) {
                            for (const [key, val] of Object.entries(value)) {
                                if (val.selected) {
                                    if (val.file == undefined || val.file.file_path == null) {
                                        return false;
                                    }
                                }
                            };
                            return true;
                        })
                    }
                }
            }
        };

        return _validation.createFieldsValidationRules(validations);
    },
    template: `
    <h3 class="font-normal">Upload artwork / select print position</h3>
    <div v-for="(location, side) in product.customisations" class="flex items-center">
        <c-input @update:model-value="product.sideToggled($event, side)" class="pr-4:1t2e pr-2" :name="'location_' + side" v-model="product.options.customisations[side].selected" type="checkbox:round" :valuelabel="location.label"></c-input>
        <c-input @click="product.setSlide(side)" v-show="product.options.customisations[side].selected" v-if="Object.keys(location.positions).length > 1" v-for="(position, position_value) in location.positions" class="pr-4:1t2e pr-2" :name="'location_' + side + '_position_' + position_value" v-model="product.options.customisations[side].position" type="radio:round" :value="position_value" :valuelabel="position.label"></c-input>
        <c-input
            @upload:start="product.setIsLoading(true)"
            @upload:end="product.setIsLoading(false)"
            @upload:cleared="product.setIsLoading(false)"
            @update:model-value="product.setSlide(side)"
            @copy_image="file => product.copyImage(file, location.copy_to)"
            @image_copied="product.clearCopyImage(side)"
            v-show="product.options.customisations[side].selected"
            class="upload__round"
            :name="'location_' + side + '_upload'"
            v-model="product.options.customisations[side].file"
            label="Upload"
            type="file:round"
            endpoint="upload/builder"
            :image_copy="product.customisations[side].image_copy"
            accept="image/*"
            :copy_to="location.copy_to">
        </c-input>
    </div>
    `
}

export default _print_locations;
