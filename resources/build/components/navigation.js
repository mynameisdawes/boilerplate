import { h } from "vue";

let _navigation = {
    name: "c-navigation",
    emits: [
        "close"
    ],
    data() {
        return {
            position: []
        };
    },
    methods: {
        activeCurrent(nav_item, nav_level) {
            if (this.position.length > 0) {
                const last_position = this.position[this.position.length - 1];
                if (last_position.nav_item === nav_item && last_position.nav_level === nav_level) {
                    return true;
                }
            }
            return false;
        },
        activePrev(nav_item, nav_level) {
            let found = false;
            if (this.position.length > 1) {
                const last_position = this.position.slice(0, -1);
                if (last_position.length > 0) {
                    last_position.forEach(position => {
                        if (position.nav_item === nav_item && position.nav_level === nav_level) {
                            found = true;
                        }
                    });
                }
            }
            return found;
        },
        getMode() {
            switch (this.mode) {
                case "sm":
                case "lg":
                    return this.mode;
                default:
                    return "r";
            }
        },
        getModeR() {
            return getComputedStyle(this.$el).getPropertyValue("--mode").trim();
        },
        navigateNext(nav_item, nav_level) {
            this.position.push({ nav_item, nav_level });
        },
        navigatePrev(nav_item, nav_level) {
            this.position = this.position.slice(0, -1);
        },
        navigateReset() {
            this.position = [];
        },
        recursiveRender(item, nav_item, nav_level) {
            let a_props = {
                onClick: (e) => {
                    if (this.getMode() === "sm" || (this.getMode() === "r" && this.getModeR() === "sm")) {
                        if (e.target.hash !== "") {
                            this.$emit("close");
                        }
                    }
                },
                href: item.href,
                role: "menuitem",
            };

            if (item.attributes !== undefined && item.attributes) {
                Object.keys(item.attributes).forEach((attribute_name) => {
                    a_props[attribute_name] = item.attributes[attribute_name];
                });
            }

            if (typeof(item.children) !== "undefined" && item.children.length > 0) {
                a_props.onClick = (e) => {
                    if (this.getMode() === "sm" || (this.getMode() === "r" && this.getModeR() === "sm")) {
                        e.preventDefault();
                        this.navigateNext(nav_item, nav_level);
                    }
                };
            }

            nav_level = nav_level + 1;

            let li_children = [ h("a", a_props, item.title) ];

            let li_props = {
                role: "none"
            };

            if (typeof(item.children) !== "undefined" && item.children.length > 0) {
                let child_items = [];

                child_items.push(h("li", {
                    // "aria-hidden": "true",
                    class: "navigation_item__back"
                }, [ h("a", {
                    onClick: (e) => {
                        e.preventDefault();
                        if (this.getMode() === "sm" || (this.getMode() === "r" && this.getModeR() === "sm")) {
                            this.navigatePrev();
                        }
                    },
                    href: "#"
                }, "Back") ]));

                if (item.href) {
                    let a_props = {
                        onClick: (e) => {
                            if (this.getMode() === "sm" || (this.getMode() === "r" && this.getModeR() === "sm")) {
                                if (e.target.hash !== "") {
                                    this.$emit("close");
                                }
                            }
                        },
                        href: item.href,
                    };

                    if (item.attributes !== undefined && item.attributes) {
                        Object.keys(item.attributes).forEach((attribute_name) => {
                            a_props[attribute_name] = item.attributes[attribute_name];
                        });
                    }

                    child_items.push(h("li", {
                        class: "navigation_item__link",
                    }, [
                        h("a", a_props, item.title)
                    ]));
                }

                let ul_props = {
                    role: "menu"
                };

                if (this.activePrev(nav_item, nav_level) === true) {
                    ul_props.class = "navigation_item--prev";
                } else if (this.activeCurrent(nav_item, nav_level) !== true) {
                    ul_props.class = "navigation_item--next";
                }

                ul_props["data-level"] = nav_level;
                ul_props["data-item"] = nav_item;
                li_props.class = "navigation_item__ancestor";

                if (typeof(item.state) !== "undefined" && typeof(item.state.right) !== "undefined" && item.state.right === true) {
                    li_props.class += " navigation_item--right";
                }

                child_items.push(item.children.map((child_item, child_nav_item) => {
                    return this.recursiveRender(child_item, child_nav_item, nav_level);
                }));

                li_children.push(h("ul", ul_props, child_items));
            }

            return h("li", li_props, li_children);
        }
    },
    props: {
        items: {
            type: Array,
            default() {
                return [
                    { title: "Link 1", href: "#link_1", children: [
                        { title: "Link 1.1", href: "#link_11", children: [
                            { title: "Link 1.1.1", href: "#link_111" },
                            { title: "Link 1.1.2", href: "#link_112" }
                        ] },
                        { title: "Link 1.2", href: "#link_12" }
                    ] },
                    { title: "Link 2", href: "#link_2" },
                    { title: "Link 3", href: "#link_3", state: { right: true }, children: [
                        { title: "Link 3.1", href: "#link_31", children: [
                            { title: "Link 3.1.1", href: "#link_311" },
                            { title: "Link 3.1.2", href: "#link_312" }
                        ] },
                        { title: "Link 3.2", href: "#link_32" }
                    ] },
                    { title: "Link 4", href: "#link_4" },
                    { title: "Link 5", href: "#link_5" }
                ];
            }
        },
        label: {
            type: String,
            default: "Document Navigation"
        },
        mode: {
            type: String,
            default: "r"
        }
    },
    render() {
        const props = this.$props;
        if (typeof(props.items) !== "undefined" && props.items.length > 0) {
            let mode_class = "document__navigation--r";
            let nav_level = 0;

            let ul_props = {
                role: "menubar",
                "aria-label": props.label
            };

            if (this.position.length > 0) {
                ul_props.class = "navigation_item--prev";
            }

            switch (this.getMode()) {
                case "sm":
                    mode_class = "document__navigation--sm";
                    break;
                case "lg":
                    mode_class = "document__navigation--lg";
                    break;
                default:
                    mode_class = "document__navigation--r";
            }

            return h("nav", {
                "aria-label": props.label,
                class: "document__navigation " + mode_class,
                role: "navigation",
            }, [
                h("ul", ul_props, props.items.map((item, nav_item) => {
                    return this.recursiveRender(item, nav_item, nav_level);
                }, this))
            ]);
        }
    }
};

export default _navigation;