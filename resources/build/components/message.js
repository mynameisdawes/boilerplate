let _message = {
    name: "c-message",
    props: {
        rememberhide: {
            type: String
        },
        autohide: {
            type: Boolean,
            default: false
        },
        trigger: {
            type: Boolean,
            default: false
        },
        content: {
            default: " "
        },
        level: {
            default: null
        },
        required: {
            type: Boolean,
            default: false
        }
    },
    watch: {
        trigger: {
            handler(new_val, old_val) {
                if (new_val != old_val) {
                    if (new_val == true) {
                        this.showAction();
                    } else {
                        this.hideAction();
                    }
                }
            },
            immediate: true
        }
    },
    data() {
        return {
            autohide_timeout_id: null,
            is_shown: false
        }
    },
    methods: {
        currentTimestamp() {
            return Math.floor(Date.now() / 1000);
        },
        shouldShowAction() {
            const rememberhide_timeout = window.localStorage.getItem(`message_rememberhide--${this.rememberhide}`);
            if (rememberhide_timeout !== null) {
                const timeout = 604800; // A week in seconds
                return parseFloat(rememberhide_timeout) < this.currentTimestamp() - timeout;
            }
            return true;
        },
        showAction() {
            if (this.is_shown === true) {
                return;
            }
            if (this.content) {
                this.is_shown = true;
                if (this.autohide == true) {
                    if (this.autohide_timeout_id) {
                        clearTimeout(this.autohide_timeout_id);
                    }
                    this.autohide_timeout_id = setTimeout(() => {
                        this.hideAction();
                    }, 5000);
                }
            }
        },
        hideAction() {
            if (this.is_shown === false) {
                return;
            }
            if (this.rememberhide !== undefined) {
                window.localStorage.setItem(`message_rememberhide--${this.rememberhide}`, this.currentTimestamp());
            }
            this.is_shown = false;
        }
    },
    created() {
        if (this.trigger === true && (this.rememberhide === undefined || this.shouldShowAction())) {
            this.showAction();
        } else {
            this.hideAction();
        }
    },
    template: `
    <div class="message" :class="[{ is_hidden: !is_shown }]">
        <div class="message__outer"><div class="message__inner">
            <div class="message__content" v-html="content"></div>
            <div class="message__dismiss" @click="hideAction" v-if="required == false"></div>
        </div></div>
    </div>
    `
};

export default _message;