import _overlay_mixin from "@/mixins/overlays.js";

let _modal = {
    name: "c-modal",
    emits: [
        "open",
        "close",
    ],
    mixins: [_overlay_mixin],
    template: `
    <div class="modal__overlay" :class="[{ is_open: is_open == true }]" @click.stop="attemptCloseAction">
        <div class= "modal__dialog">
            <div class="modal__inner" @click.stop>
                <div class="modal__dismiss" @click.stop="attemptCloseAction" v-if="required == false"></div>
                <div class="modal__content">
                    <slot></slot>
                </div>
                <div class="modal__content__after">
                    <slot name="after_content"></slot>
                </div>
            </div>
        </div>
    </div>
    `
};

let _confirmation = {
    name: "c-confirmation",
    emits: [
        "confirm",
        "cancel",
        "open",
        "close",
    ],
    mixins: [_overlay_mixin],
    props: {
        confirm: {
            type: String,
            default: ""
        },
        cancel: {
            type: String,
            default: ""
        }
    },
    methods: {
        confirmAction() {
            this.$emit("confirm");
            this.closeAction();
        },
        cancelAction() {
            this.$emit("cancel");
            this.closeAction();
        },
        escapeAction() {
            this.cancelAction();
        }
    },
    template: `
    <div class="modal__overlay" :class="{ is_open: is_open == true }" @click.stop="cancelAction">
        <div class= "modal__dialog">
            <div class="modal__inner" @click.stop>
                <div class="modal__dismiss" @click.stop="cancelAction"></div>
                <div class="modal__content">
                    <slot></slot>
                </div>
                <div class="modal__action">
                    <div class="collection toolbar">
                        <a href="#" class="btn bg-secondary border-secondary text-secondary_contrasting rounded-none flex-grow" @click.stop.prevent="cancelAction" v-html="cancel"></a><a href="#" class="btn bg-primary border-primary text-primary_contrasting rounded-none flex-grow" @click.stop.prevent="confirmAction" v-html="confirm"></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    `
};

export {
    _modal,
    _confirmation
};