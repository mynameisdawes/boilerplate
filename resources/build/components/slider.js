import { h } from "vue";

let _slider = {
    name: "c-slider",
    props: {
        trigger: {
            type: Boolean,
            default: false
        },
        mode: {
            type: String,
            default: "r"
        }
    },
    data() {
        return {
            is_open: false,
            mode_variable: null,
        };
    },
    watch: {
        trigger: {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    if (new_val == true) {
                        this.openAction();
                    } else {
                        this.closeAction();
                    }
                }
            },
            immediate: true
        }
    },
    methods: {
        getMode() {
            switch (this.mode) {
                case "sm":
                case "lg":
                    return this.mode;
                default:
                    return "r";
            }
        },
        openAction() {
            this.$emit("open");
            this.is_open = true;
        },
        closeAction() {
            this.$emit("close");
            this.is_open = false;
        },
    },
    render() {
        let mode_class = null;
        switch (this.getMode()) {
            case "sm":
                mode_class = "document__slider--sm";
                break;
            case "lg":
                mode_class = "document__slider--lg";
                break;
            default:
                mode_class = "document__slider--r";
        }

        return h("div", {
            onClick(e) {
                e.stopPropagation();
            },
            class: "document__slider " + mode_class +
            ((this.is_open == true) ? " is_open" : ""),
            inert: !this.is_open && this.mode_variable == 'sm',
        }, this.$slots.default());
    },
    mounted() {
        const updateModeVar = () => {
            if (this.$el) {
                this.mode_variable = getComputedStyle(this.$el).getPropertyValue('--mode').trim();
            }
        };

        updateModeVar();
        window.addEventListener('resize', updateModeVar);
    },
    beforeUnmount() {
        window.removeEventListener('resize', updateModeVar);
    },
};

export default _slider;