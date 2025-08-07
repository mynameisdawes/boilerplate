import { useVuelidate } from "@vuelidate/core";

import _config from "@/utilities/config.js";
_config.init();

import { _api, _storage } from "@/utilities/api.js";

let _form = {
    name: "c-form",
    setup () {
        const v$ = useVuelidate();
        return { v$ };
    },
    props: {
        name: {
            type: String,
            default() {
                return this.ref;
            },
        },
        method: {
            type: String,
            default: "get",
        },
        action: {
            type: String,
        },
        field_values: {
            type: Object,
            required: true,
        },
        field_storage: {
            type: Object,
            default() {
                return {};
            },
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
        is_valid: {
            type: Boolean,
            default: true,
        },
        mounted_override: {
            type: Function,
        },
        submit_override: {
            type: Function,
        },
        clear_fields: {
            type: Boolean,
            default: false,
        },
    },
    data() {
        return {
            is_disabled: false,
            is_loading: false,
            field_defaults: {},
            response: {
                success: false,
                success_message: null,
                error: false,
                error_message: null,
                http_code: null,
                http_message: null,
                data: null,
            },
        };
    },
    validations() {
        return { field_values: this.field_validation_rules };
    },
    watch: {
        field_values: {
            handler() {
                this.setStorage();
            },
            deep: true
        }
    },
    methods: {
        currentTimestamp() {
            return Math.floor(Date.now() / 1000);
        },
        dataToFormData(data, parent_form, parent_prop) {
            let form_data = parent_form || new FormData();

            if (data && typeof data === 'object' && !(data instanceof Date) && !(data instanceof File) && !(data instanceof Blob)) {
                Object.keys(data).forEach(name => {
                    this.dataToFormData(data[name], form_data, parent_prop ? `${parent_prop}[${name}]` : name);
                });
            } else {
                const value = data == null ? '' : data;
                form_data.append(parent_prop, value);
            }

            return form_data;
        },
        dataToFormInputs(data, form, parent_prop) {
            for (let name in data) {
                let input = document.createElement("input");
                input.type = "hidden";
                input.name = (typeof parent_prop !== "undefined") ? `${parent_prop}[${name}]` : name;
                if (typeof data[name] === "object" && data[name] !== null) {
                    this.dataToFormInputs(data[name], form, input.name);
                } else {
                    input.value = data[name];
                    form.appendChild(input);
                }
            }
        },
        getAction() {
            if (this.action) {
                const action = _config.get(this.action);
                return action ? action : this.action;
            }
            return null;
        },
        getStorage() {
            const updated_at = window.localStorage.getItem(`${this.name}--timeout`);
            if (updated_at !== null) {
                const timeout = 1800; // Half an hour in seconds
                const clear_data = parseFloat(updated_at) < this.currentTimestamp() - timeout;
                if (clear_data === true) {
                    window.localStorage.removeItem(`${this.name}--fields`);
                    window.localStorage.removeItem(`${this.name}--timeout`);
                } else {
                    if (
                        this.method.toLowerCase() == "post"
                        || this.method.toLowerCase() == "get"
                    ) {
                        const stored_value = window.localStorage.getItem(`${this.name}--fields`);
                        if (stored_value != null) {
                            const storage_data = JSON.parse(stored_value);
                            for (let field_property in this.field_values) {
                                if (
                                    this.field_values.hasOwnProperty(field_property)
                                    && typeof(this.field_values[field_property]) !== "undefined"
                                    && typeof(this.field_storage[field_property]) !== "undefined"
                                    && this.field_storage[field_property]
                                    && typeof(storage_data[field_property]) !== "undefined"
                                ) {
                                    this.field_values[field_property] = storage_data[field_property];
                                }
                            }
                        }
                    }
                }
            }
        },
        setStorage() {
            if (this.method.toLowerCase() === "post" || this.method.toLowerCase() === "get") {
                const storage_data = {};
                for (let field_property in this.field_values) {
                    if (
                        this.field_values.hasOwnProperty(field_property)
                        && typeof(this.field_values[field_property]) !== "undefined"
                        && typeof(this.field_storage[field_property]) !== "undefined"
                        && this.field_storage[field_property] == true
                    ) {
                        storage_data[field_property] = this.field_values[field_property];
                    }
                }
                window.localStorage.setItem(`${this.name}--fields`, JSON.stringify(storage_data));
                if (window.localStorage.getItem(`${this.name}--timeout`) === null
                ) {
                    window.localStorage.setItem(`${this.name}--timeout`, this.currentTimestamp());
                }
            }
        },
        clearStorage() {
            window.localStorage.removeItem(`${this.name}--fields`);
            if (
                window.localStorage.getItem(`${this.name}--timeout`) !==
                null
            ) {
                window.localStorage.removeItem(`${this.name}--timeout`);
            }
        },
        clearFields(reset) {
            Object.keys(this.field_values).forEach((field_property) => {
                if (
                    this.field_values.hasOwnProperty(field_property) &&
                    this.field_defaults.hasOwnProperty(field_property)
                ) {
                    this.field_values[field_property] = this.field_defaults[field_property];
                }
            });
            if (reset !== undefined && reset === true) {
                this.v$.$reset();
            }
        },
        validate() {
            this.v$.$touch();
        },
        load() {
            this.is_loading = true;
            this.response.success = false;
            this.response.error = false;
            this.clearResponse();
        },
        unload() {
            this.is_loading = false;
        },
        redirect(response) {
            let response_has_data = Object.keys(response.data.data).some((prop) => prop !== "redirect_url");
            if (response.data.data.redirect_type !== undefined) {
                if (response.data.data.redirect_type == 'get') {
                    response_has_data = false;
                }
                if (response.data.data.redirect_type == 'post') {
                    response_has_data = true;
                }
            }
            if (response_has_data === true) {
                let form = document.createElement("form");
                form.style.display = "none";
                form.method = "post";
                form.action = response.data.data.redirect_url;
                const meta_csrf = document.querySelector("meta[name='csrf-token']");
                if (meta_csrf) {
                    response.data._token = meta_csrf.getAttribute("content");
                }
                this.dataToFormInputs(response.data, form);
                document.body.appendChild(form);
                form.submit();
            } else {
                window.location.href = response.data.data.redirect_url;
            }
        },
        clearResponse() {
            setTimeout(() => {
                this.response.success = false;
                this.response.success_message = null;
                this.response.error = false;
                this.response.error_message = null;
                this.response.http_code = null;
                this.response.http_message = null;
                this.response.data = null;
            }, 300);
        },
        setResponse(response) {
            setTimeout(() => {
                this.response.success = response.data.success;
                this.response.success_message = response.data.success_message;
                this.response.error = response.data.error;
                this.response.error_message = response.data.error_message;
                this.response.http_code = response.data.http_code;
                this.response.http_message = response.data.http_message;
                this.response.data = response.data.data;
            }, 301);
        },
        successResponse(response) {
            this.unload();
            this.setResponse(response);
            if (
                response.data.data !== null
                && typeof(response.data.data.redirect_url) !== "undefined"
                && response.data.data.redirect_url !== ""
            ) {
                this.redirect(response);
            } else {
                if (this.clear_fields === true) {
                    this.clearFields();
                    this.v$.field_values.$reset();
                }
            }
            this.clearStorage();
            this.is_disabled = false;
            this.$emit("success", response);
        },
        failResponse(response) {
            this.unload();
            this.setResponse(response);
            this.is_disabled = false;
            this.$emit("fail", response);
        },
        submit() {
            if (this.is_disabled === true) { return; }
            if (this.is_valid === true) {
                if (typeof(this.submit_override) !== "undefined") {
                    this.submit_override(this);
                } else {
                    this.is_disabled = true;
                    this.validate();
                    if (this.v$.$error === false) {
                        if (this.getAction()) {
                            const data = this.dataToFormData(this.field_values);

                            this.load();
                            _api.request({
                                url: this.getAction(),
                                method: this.method,
                                data: data,
                            })
                            .then((response) => {
                                if (_storage.isSuccess(response)) {
                                    this.successResponse(response);
                                } else {
                                    this.failResponse(response);
                                }
                            })
                            .catch((error) => {
                                this.failResponse(error.response);
                            });
                        } else {
                            this.is_disabled = true;
                            let response = {
                                success: true,
                                success_message: null,
                                error: false,
                                error_message: null,
                                http_code: 200,
                                http_message: 'OK',
                                data: this.field_values
                            }

                            this.setResponse(response);
                            this.$emit("success", response);
                            this.is_disabled = false;
                        }
                    } else {
                        this.is_disabled = false;
                    }
                }
            }
        },
    },
    created() {
        this.field_defaults = JSON.parse(JSON.stringify(this.field_values));
        this.getStorage();
    },
    mounted() {
        if (typeof(this.mounted_override) !== "undefined") {
            this.mounted_override(this);
        }
    },
    template: `
    <div class="form__wrapper">
        <div class="spinner__wrapper spinner--absolute-not" :class="{ is_loading: is_loading == true }">
            <div class="spinner"></div>
        </div>
        <form @submit.prevent="submit" novalidate>
            <slot
            name="fields"
            :v$="v$"
            :field_values="field_values"
            :validation_rules="v$.field_values"
            :validation_messages="field_validation_messages"
            :response="response"

            :validate="validate"
            :load="load"
            :unload="unload"
            :successResponse="successResponse"
            :failResponse="failResponse"></slot>
        </form>
    </div>
    `
};

export default _form;