import { useVuelidate } from "@vuelidate/core";

let _form_segment = {
    name: "c-form_segment",
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    props: {
        field_values: {
            type: Object,
            required: true,
        },
        field_validation_rules: {
            type: Object,
            default() {
                return {};
            },
        },
        field_validation_messages: {
            type: Object,
            default() {
                return {};
            },
        },
    },
    data() {
        return {};
    },
    validations() {
        return { field_values: this.field_validation_rules };
    },
    methods: {
    },
    template: `
    <slot
    :v$="v$"
    name="fields"
    :field_values="field_values"
    :validation_rules="v$.field_values"
    :validation_messages="field_validation_messages"
    ></slot>
    `
};

export default _form_segment;