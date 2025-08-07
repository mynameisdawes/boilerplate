import _config from "@/utilities/config.js";
_config.init();

import { CookieStorage } from "cookie-storage";
const cookieStorage = new CookieStorage();

let _color_scheme = {
    name: "c-color_scheme",
    data() {
        return {
            popup: false,
            scheme_name: 'default',
            scheme: null,
            field_values: {
                scheme: false,
            },
            htmlElement: null,
            colorSchemeModes: ["default", "system", "light", "dark"],
            localColorScheme: localStorage.getItem("color-scheme"),
            defaultColorSchemeMode: null,
            systemPreferenceColorScheme: null,
        };
    },
    computed: {
        checkboxColorSchemeModes() {
            return this.colorSchemeModes.filter((colorSchemeMode) => {
                return !["default", "system", this.defaultColorSchemeMode].includes(colorSchemeMode);
            })
        }
    },
    methods: {
        toggleColorScheme() {
            if (this.field_values.scheme === true) {
                if (this.checkboxColorSchemeModes.length > 0) {
                    this.htmlElement.setAttribute("color-scheme", this.checkboxColorSchemeModes[0]);
                    localStorage.setItem("color-scheme", this.checkboxColorSchemeModes[0]);
                } else {
                    this.htmlElement.removeAttribute("color-scheme");
                    localStorage.removeItem("color-scheme");
                }
            } else {
                this.htmlElement.removeAttribute("color-scheme");
                localStorage.removeItem("color-scheme");
            }
        },
        setColorScheme(scheme) {
            if (this.colorSchemeModes.includes(scheme)) {
                this.scheme_name = scheme;
                this.scheme = null;
                if (scheme == "default") {
                    this.htmlElement.removeAttribute("color-scheme");
                    localStorage.removeItem("color-scheme");
                } else if (scheme == "system") {
                    if (window.matchMedia) {
                        if (this.systemPreferenceColorScheme.matches) {
                            this.htmlElement.setAttribute("color-scheme", "dark");
                            this.scheme = "dark";
                        } else {
                            this.htmlElement.setAttribute("color-scheme", "light");
                            this.scheme = "light";
                        }
                    }
                    localStorage.setItem("color-scheme", "system");
                } else {
                    this.htmlElement.setAttribute("color-scheme", scheme);
                    localStorage.setItem("color-scheme", scheme);
                    this.scheme = scheme;
                }

                if (this.scheme && this.checkboxColorSchemeModes.includes(this.scheme)) {
                    this.field_values.scheme = true;
                } else {
                    this.field_values.scheme = false;
                }
            }
        },
        togglePopup() {
            this.popup = !this.popup;
        },
        closePopup() {
            this.popup = false;
        }
    },
    mounted() {
        this.htmlElement = document.querySelector("html");

        if (this.htmlElement) {
            document.addEventListener("click", this.closePopup);

            this.scheme = null;
            this.defaultColorSchemeMode = window.getComputedStyle(this.htmlElement).getPropertyValue("color-scheme");

            this.systemPreferenceColorScheme = window.matchMedia("(prefers-color-scheme: dark)");

            if (this.localColorScheme == "system") {
                this.scheme_name = this.localColorScheme;
                if (window.matchMedia) {
                    if (this.systemPreferenceColorScheme.matches) {
                        this.htmlElement.setAttribute("color-scheme", "dark");
                        this.scheme = "dark";
                    } else {
                        this.htmlElement.setAttribute("color-scheme", "light");
                        this.scheme = "light";
                    }
                }
            } else if (this.localColorScheme !== null) {
                this.scheme_name = this.localColorScheme;
                this.htmlElement.setAttribute("color-scheme", this.localColorScheme);
                this.scheme = this.localColorScheme;
            }

            if (this.scheme && this.checkboxColorSchemeModes.includes(this.scheme)) {
                this.field_values.scheme = true;
            } else {
                this.field_values.scheme = false;
            }

            this.systemPreferenceColorScheme.addEventListener("change", () => {
                this.scheme = null;
                this.localColorScheme = localStorage.getItem("color-scheme");
                this.scheme_name = this.localColorScheme;

                if (this.localColorScheme == "system") {
                    if (this.systemPreferenceColorScheme.matches) {
                        this.htmlElement.setAttribute("color-scheme", "dark");
                        this.scheme = "dark";
                    } else {
                        this.htmlElement.setAttribute("color-scheme", "light");
                        this.scheme = "light";
                    }
                }

                if (this.scheme && this.checkboxColorSchemeModes.includes(this.scheme)) {
                    this.field_values.scheme = true;
                } else {
                    this.field_values.scheme = false;
                }
            });
        }
    },
    template: `
    <slot
    :togglePopup="togglePopup"
    :popup="popup"
    :scheme_name="scheme_name"
    :scheme="scheme"
    :localColorScheme="localColorScheme"
    :toggleColorScheme="toggleColorScheme"
    :setColorScheme="setColorScheme"
    :field_values="field_values"
    ></slot>
    `,
};

export default _color_scheme;