import _config from "@/utilities/config.js";
_config.init();

import _utilities from "@/utilities/utilities.js";

let _validation;
import("@/utilities/validation.js").then((exports) => { _validation = exports.default; });
import { useVuelidate } from "@vuelidate/core";

import _message from "./message.js";

let _steps = {
    name: "c-steps",
    components: {
        "c-message": _message
    },
    emits: [
        "submit",
        "step_change"
    ],
    setup (props, context) {
        let v$ = useVuelidate();

        return { v$ };
    },
    data() {
        return {
            active_step: 1,
            steps: []
        };
    },
    computed: {
        is_disabled() {
            return this.v$.$invalid || this.v$.$error;
        },
        step_count() {
            return this.steps.length;
        }
    },
    methods: {
        goToStep(step_dest) {
            if (step_dest == (this.active_step + 1) && this.active_step < this.step_count) {
                this.v$.$touch();
                if (this.v$.$error === false) {
                    this.active_step = step_dest;
                    this.$emit("step_change", step_dest);
                }
            } else if (step_dest < this.active_step && this.active_step > 1) {
                this.active_step = step_dest;
                this.$emit("step_change", step_dest);
            }
        },
        nextStep() {
            this.goToStep(this.active_step + 1);
        },
        prevStep() {
            this.goToStep(this.active_step - 1);
        },
        submit() {
            this.$emit('submit');
        },
    },
    created() {
    },
    template: `
    <slot
    name="steps_progress"
    :active_step="active_step"
    :steps="steps"
    :goToStep="goToStep"
    ></slot>

    <slot
    name="steps_content"
    :active_step="active_step"
    ></slot>

    <slot
    name="steps_navigation"
    :active_step="active_step"
    :steps="steps"

    :v$="v$"

    :is_disabled="is_disabled"
    :step_count="step_count"

    :nextStep="nextStep"
    :prevStep="prevStep"
    :submit="submit"
    ></slot>
    `,
};

export default _steps;