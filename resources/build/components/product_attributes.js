import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _product_attributes = {
    name: "c-product_attributes",
    emits: [
        "loaded:attributes"
    ],
    props: {
        is_enabled: {
            type: Boolean,
            default: _config.get('shop.filters.enabled')
        },
        interact_with_url: {
            type: Boolean,
            default: true
        },
        pre_filters: {
            type: Object,
            default: {}
        },
        attribute_names: {
            type: Array,
            default: () => []
        },
        attribute_names_excluded: {
            type: Array,
            default: () => []
        },
    },
    data() {
        return {
            is_loading: false,
            attributes_fetched: false,
            attributes: [],
            filters: [],
            filter_fields: null,
            filters_watcher_bypassed: false,
            options: {}
        };
    },
    methods: {
        getProductAttributes(data) {
            let payload = {};
            if (data !== undefined) {
                payload.data = data;
            } else {
                payload.data = {};
            }

            if (payload.data !== undefined && Object.keys(this.pre_filters).length > 0) {
                payload.data.required_values = this.pre_filters;
            }

            if (payload.data !== undefined && this.attribute_names.length > 0) {
                payload.data.attribute_names = this.attribute_names;
            }

            if (payload.data !== undefined && this.attribute_names_excluded.length > 0) {
                payload.data.attribute_names_excluded = this.attribute_names_excluded;
            }

            if (_config.get("shop.product_type")) {
                if (payload.data.attribute_names_excluded === undefined) {
                    payload.data.attribute_names_excluded = [];
                }
                payload.data.attribute_names_excluded.push('product_type');
            }

            this.is_loading = true;

            return _storage.post(_config.get("api.product_attributes.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.attributes = response.attributes;
                    if (this.attributes.length > 0) {
                        this.attributes.forEach((attribute) => {
                            if (this.filter_fields === null) {
                                this.filter_fields = {};
                            }
                            if (this.filter_fields[attribute.name] === undefined) {
                                this.filter_fields[attribute.name] = {...attribute, model: ""};
                            }
                            this.convertAttributesToOptions(attribute.name);
                        });
                    }

                    this.updateFromUrl();
                    this.attributes_fetched = true;
                }
                this.is_loading = false;
            }, payload);
        },
        convertAttributesToOptions(attribute_name) {
            if (!this.options[attribute_name]) {
                let options = [];
                let matching_attributes = this.attributes.filter((attribute) => {
                    return attribute.name === attribute_name;
                });

                if (matching_attributes.length > 0 && matching_attributes[0].attributes.length > 0) {
                    matching_attributes[0].attributes.forEach((attribute) => {
                        let option = {
                            text: attribute.value_label,
                            value: attribute.value,
                            configuration: attribute.configuration,
                        };
                        options.push(option);
                    });
                }
                this.options[attribute_name] = options;
            }

            return this.options[attribute_name];
        },
        handleHistoryChange() {
            if (!this.attributes_fetched) {
                return;
            }

            this.filters_watcher_bypassed = true;

            const _url_params = new URLSearchParams(window.location.search);

            if (this.filter_fields !== null) {
                Object.values(this.filter_fields).forEach((field) => {
                    if (_url_params.get(field.name)) {
                        field.model = _url_params.get(field.name);
                    } else {
                        field.model = "";
                    }
                });
            }

            this.$nextTick(() => {
                this.filters_watcher_bypassed = false;
            });
        },
        updateFromUrl() {
            if (this.attributes_fetched === true) {
                return;
            }

            const _url_params = new URLSearchParams(window.location.search);
            const url_params = new URLSearchParams(window.location.search);

            if (this.filter_fields !== null) {
                Object.values(this.filter_fields).forEach((field) => {
                    if (_url_params.get(field.name)) {
                        field.model = _url_params.get(field.name);
                        url_params.set(field.name, field.model);
                    }
                });

                url_params.sort();
                const _url = new URL(window.location.origin + window.location.pathname);
                _url.search = url_params.toString();
                if (this.interact_with_url) {
                    window.history.pushState({}, "", _url.toString());
                }
            }
        },
        updateToUrl() {
            if (this.attributes_fetched === false) {
                return;
            }

            const url_params = new URLSearchParams(window.location.search);

            if (this.filter_fields !== null) {
                Object.values(this.filter_fields).forEach((field) => {
                    url_params.delete(field.name);
                    if (field.model !== "") {
                        url_params.set(field.name, field.model);
                    }
                });

                url_params.sort();
                const _url = new URL(window.location.origin + window.location.pathname);
                _url.search = url_params.toString();
                if (this.interact_with_url) {
                    window.history.pushState({}, "", _url.toString());
                }
            }
        },
        updateFilters() {
            if (this.filter_fields !== null) {
                const filters = [];
                Object.values(this.filter_fields).forEach((field) => {
                    if (field.model !== "") {
                        filters.push({
                            attribute_name: field.name,
                            value: field.model,
                        });
                    }
                });

                this.filters = filters;
            }
        }
    },
    watch: {
        attribute_names: {
            handler(new_val, old_val) {
                if (!_.isEqual(new_val, old_val)) {
                    if (this.is_enabled) {
                        this.getProductAttributes();
                    }
                }
            }
        },
        attribute_names_excluded: {
            handler(new_val, old_val) {
                if (!_.isEqual(new_val, old_val)) {
                    if (this.is_enabled) {
                        this.getProductAttributes();
                    }
                }
            }
        },
        filter_fields: {
            deep: true,
            handler() {
                this.updateFilters();
            }
        },
        filters: {
            handler(new_val, old_val) {
                if (!this.filters_watcher_bypassed) {
                    this.updateToUrl();
                }
            }
        }
    },
    created() {
        if (this.is_enabled) {
            this.getProductAttributes();
        }
    },
    mounted() {
        window.addEventListener("popstate", this.handleHistoryChange);
    },
    beforeDestroy() {
        window.removeEventListener("popstate", this.handleHistoryChange);
    },
    template: `
    <slot
        :is_enabled="is_enabled"
        :is_loading="is_loading"

        :attributes_fetched="attributes_fetched"
        :attributes="attributes"
        :filter_fields="filter_fields"
        :filters="filters"

        :options="options"

        :getProductAttributes="getProductAttributes"
        :convertAttributesToOptions="convertAttributesToOptions"
    ></slot>
    `
};

export default _product_attributes;
