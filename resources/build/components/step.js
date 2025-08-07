import _config from "@/utilities/config.js";
_config.init();

import _utilities from "@/utilities/utilities.js";

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; });
import { useVuelidate } from "@vuelidate/core";

import { ref, watch } from "vue";

let _step = {
    name: "c-step",
    props: {
        display_step: {
            type: Number,
            default: 1
        },
        name: {
            type: String,
            required: false
        }
    },
    setup (props, context) {
        const validations = {};
        const state = {};
        const v$ = useVuelidate({$registerAs: "step_" + props.display_step});
        return { v$ };
    },
    data() {
        return {
            test: {}
        }
    },
    computed: {
        active_step() {
            return this.$parent.active_step;
        }
    },
    methods: {
    },
    created() {
        this.$parent.steps.push(this);
    },
    template: `
    <div v-if="display_step <= active_step" v-show="display_step == active_step">
        <slot
        :validate="active_step >= display_step"
        ></slot>
    </div>
    `,
};

export default _step;