let _tab = {
    name: "c-tab",
    props: {
        name: {
            type: String,
            required: true
        },
        label: {
            type: String,
            required: true
        },
    },
    methods: {
        isActive() {
            return this.$parent.active_tab == this.name;
        },
        setToActive() {
            this.$parent.setActive(this.name);
        }
    },
    template: `
    <div class="tab" :class="{ is_active: isActive() }">
        <header class="tab__header" @click="setToActive">
            <a href="#" @click.prevent="" v-html="label"></a>
        </header>
        <section class="tab__content">
            <slot></slot>
        </section>
    </div>
    `
};

let _tabs = {
    name: "c-tabs",
    props: {
        active_tab: {
            type: String,
            required: true
        }
    },
    methods: {
        setActive(name) {
            this.$emit("active-tab", name);
        }
    },
    template: `
    <div class="tabs">
        <slot :active_tab="active_tab"></slot>
    </div>
    `
};

export {
    _tab,
    _tabs
};