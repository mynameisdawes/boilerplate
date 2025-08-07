import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

import _validation from "@/utilities/validation.js";
import _product_utilities from "@/utilities/products.js";

import { default as c_input } from "@/components/input.js";
import { default as c_panel_expand } from "@/components/panel_expand.js";
import { default as c_tooltip } from "@/components/tooltip.js";

let _option_mixin = {
    props: {
        step: {
            type: Number,
            required: false
        },
        product: {
            type: Object
        },
        validate: {
            required: false
        }
    },
    emits: [],
    components: {
        "c-input": c_input,
        "c-panel_expand": c_panel_expand,
        "c-tooltip": c_tooltip
    },
    data() { return {}; },
    methods: {},
    computed: {
        showOption() {
            if (this.step === undefined) return true;
            return this.step == product.active_step;
        }
    },
    validations() {},
    watch: {},
    created() {},
}

export default _option_mixin;