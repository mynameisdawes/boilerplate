let _contents = {
    name: "c-contents",
    props: {
        items: {
            type: Array,
            required: true
        }
    },
    data() {
        return {
            is_active: false
        };
    },
    methods: {
        toggleActive() {
            this.is_active = !this.is_active;
        }
    },
    template: `
    <aside class="sidebar">
        <div class="sidebar__navigation">
            <header @click="toggleActive" :class="{ is_active: is_active == true }">Scroll To</header>
            <nav>
                <ul>
                    <li @click="is_active = false" v-for="item in items">
                        <a :href="'#' + item.name" v-html="item.label"></a>
                    </li>
                </ul>
            </nav>
        </div>
    </aside>
    `
};

export default _contents;