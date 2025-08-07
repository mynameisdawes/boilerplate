let _panel_expand = {
    name: "c-panel_expand",
    data() {
        return {
            internal_is_expanded: true
        }
    },
    props: {
        is_expanded: {
            type: Boolean,
            default: true
        }
    },
    methods: {
        toggle() {
            this.internal_is_expanded = !this.internal_is_expanded;
        }
    },
    watch: {
        is_expanded: {
            handler(new_val, old_val) {
                if (old_val !== new_val) {
                    this.internal_is_expanded = new_val;
                }
            },
            immediate: true
        }
    },
    template: `
    <slot
    name="methods_above"
    :toggle="toggle"
    :is_expanded="internal_is_expanded"
    ></slot>
    <div v-bind="$attrs" class="expand__panel" :class="{ is_collapsed: !internal_is_expanded }">
        <div class="expand__panel--outer">
            <div class="expand__panel--inner">
                <slot></slot>
            </div>
        </div>
    </div>
    <slot
    name="methods_below"
    :toggle="toggle"
    :is_expanded="internal_is_expanded"
    ></slot>
    `
};

export default _panel_expand;