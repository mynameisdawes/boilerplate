import { computed } from "vue";

import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _input = {
    name: "c-input",
    emits: [
        "focus",
        "blur",
        "update:model-value",
        "update:modelValue",
        "fetch",
        "select",
        "nosuggestions",
        "colour_selected",
        "copy_image",
        "image_copied",
        "upload:start",
        "upload:end",
        "upload:cleared"
    ],
    props: {
        onFetch: {
            type: Function,
            default: null,
        },
        onNosuggestions: {
            type: Function,
            default: null,
        },
        label: {
            type: String,
            default: ""
        },
        hint: {
            type: String,
            default: ""
        },
        select_label: {
            type: String,
            default: ""
        },
        name: {
            type: String,
            required: true
        },
        type: {
            type: String,
            default: "text"
        },
        placeholder: {
            type: String
        },
        pattern: {
            type: String
        },
        increment: {
            type: Number,
            default: 1
        },
        max: {
            type: Number,
            default: 999999999
        },
        min: {
            type: Number,
            default: 0
        },
        multiple: {
            type: Boolean,
            default: false
        },
        value: {
            type: [Number, String]
        },
        value_tmp: {
            type: String
        },
        valuelabel: {
            type: String
        },
        maxlength: {
            type: Number
        },
        collection: {
            type: Boolean,
            default: false
        },
        modelValue: {
            required: true
        },
        validationrule: {
            type: Object
        },
        validationmsg: {
            type: Object
        },
        autocomplete: {
            type: String
        },
        options: {
            type: Array,
            default() {
                return [];
            }
        },
        suggestions: {
            type: Array,
            default() {
                return [];
            }
        },
        suggestions_model: {
            type: Boolean,
            default: true
        },
        suggestions_fuzzy_match: {
            type: Boolean,
            default: false
        },
        select_first_suggestion: {
            type: Boolean,
            default: false
        },
        nosuggestions: {
            type: Boolean,
            default: true
        },
        autotab: {
            type: Boolean,
            default: false
        },
        autofocus: {
            type: Boolean,
            default: false
        },
        endpoint: {
            type: String
        },
        preview: {
            type: Boolean,
            default: false
        },
        readonly: {
            type: Boolean,
            default: false
        },
        disabled: {
            type: Boolean,
            default: false
        },
        disable_buttons_minus: {
            type: Boolean,
            default: false
        },
        disable_buttons_plus: {
            type: Boolean,
            default: false
        },
        image_copy: {
            type: Object,
            required: false
        },
        copy_to: {
            type: String,
            required: false
        },
        accept: {
            type: String,
            required: false
        },
        allow_null: {
            type: Boolean,
            default: true
        }
    },
    setup(props, context) {
        let publicProperties = {};

        const model = computed({
            get() {
                return props.modelValue;
            },
            set(value) {
                return context.emit("update:modelValue", value);
            }
        });

        publicProperties.model = model;

        return publicProperties;
    },
    data() {
        return {
            uid: Date.now().toString(36) + Math.random().toString(36).substring(2),
            upload_max_filesize_bytes: _config.get('php.upload_max_filesize_bytes'),
            internal_error: null,
            suggestions_pending: true,
            suggestion_idx: -1,
            suggestions_open: false,
            suggestion_value: "",
            password_shown: false,
            dragging: false,
            uploads: [],
        };
    },
    watch: {
        modelValue: {
            handler(new_val, old_val) {
                if (this.isAutocomplete() && new_val != old_val) {
                    let suggestion_found = this.findMatchingSuggestion(new_val);
                    if (suggestion_found) {
                        this.suggestion_value = suggestion_found.text;
                    } else {
                        if (this.suggestions_model == false && this.suggestion_value != "") {
                            this.suggestion_value = new_val;
                        }
                        if (new_val === "") {
                            this.suggestion_value = "";
                        }
                    }
                }
            },
            immediate: true
        },
        value_tmp(new_val, old_val) {
            if (new_val != old_val) {
                if (new_val == null) {
                    new_val = "";
                }
                this.suggestion_value = new_val;
            }
        },
        suggestions: {
            handler(new_val, old_val) {
                if (this.isAutocomplete() && new_val != old_val) {
                    this.suggestions_pending = false;

                    if (new_val.length > 0 && this.modelValue != "" && this.suggestion_value == "") {
                        let suggestion_found = this.findMatchingSuggestion(this.modelValue);
                        if (suggestion_found) {
                            this.suggestion_value = suggestion_found.text;
                        } else {
                            if (this.suggestions_model == false && this.suggestion_value == "") {
                                this.suggestion_value = this.modelValue;
                            }
                        }
                    }
                }
            },
            immediate: true
        },
        options: {
            handler(new_val, old_val) {
                if (this.model !== undefined) {
                    if (this.model == null || this.model == "" || this.model == []) {
                        if (this.isSelect()) {
                            if (new_val && new_val.length == 1) {
                                this.updateValue(new_val[0].value);
                            }
                        } else if (this.isMultiSelectColors()) {
                            this.$emit("update:modelValue", this.model.concat([new_val[0]]));
                        } else if (this.isSelectColors()) {
                            this.updateValue(new_val[0].value);
                        }
                    }
                }
            },
            deep: true,
            immediate: true
        },
        image_copy: {
            handler(new_val) {
                if (new_val != null) {
                    this.uploads = [{
                        file: new_val.file,
                        name: new_val.file.name,
                        type: new_val.file.type,
                        size: new_val.file.size,
                        url: URL.createObjectURL(new_val.file),
                        progress: 100,
                        uploaded: true,
                        server_file_name: new_val.name,
                        server_file_path: new_val.path,
                        server_file_extension: new_val.extension,
                    }];
                    this.$emit("image_copied");
                }
            },
            deep: true
        }
    },
    methods: {
        formatBytes(bytes, decimals = 0) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(decimals)) + ' ' + sizes[i];
        },
        focusNextElement() {
            let focussableElements = "a:not([disabled]), button:not([disabled]), input[type=text]:not([disabled]), [tabindex]:not([disabled]):not([tabindex='-1'])";
            if (document.activeElement) {
                let focussable = Array.prototype.filter.call(document.querySelectorAll(focussableElements), (element) => {
                    return element.offsetWidth > 0 || element.offsetHeight > 0 || element === document.activeElement
                });
                let index = focussable.indexOf(document.activeElement);
                if (index > -1) {
                    let nextElement = focussable[index + 1] || focussable[0];
                    nextElement.focus();
                }
            }
        },
        triggerValidation() {
            this.$emit("blur");
            if (typeof(this.validationrule) !== "undefined") {
                this.validationrule.$touch();
            }
        },
        removeFile(index) {
            let upload = this.uploads[index];
            let values = null;
            if (this.isMultipleFile()) {
                values = [];
            }
            if (upload.uploaded) {
                let endpoint = _config.get(this.endpoint);

                _storage.delete((endpoint !== null) ? endpoint : this.endpoint, (_response) => {
                    URL.revokeObjectURL(upload.url);
                    this.uploads.splice(index, 1);
                    document.querySelector("#" + this.uid).value = "";
                    if (this.uploads.length > 0) {
                        this.uploads.forEach((upload) => {
                            if (this.isMultipleFile()) {
                                values.push({
                                    file_name: upload.server_file_name,
                                    file_path: upload.server_file_path,
                                    file_extension: upload.server_file_extension,
                                });
                            } else {
                                values = {
                                    file_name: upload.server_file_name,
                                    file_path: upload.server_file_path,
                                    file_extension: upload.server_file_extension,
                                };
                            }
                        });
                    }
                    this.updateValue(values);
                    this.$emit("upload:cleared");
                }, {
                    data: {
                        server_file_name: upload.server_file_name
                    }
                });
            } else {
                URL.revokeObjectURL(upload.url);
                this.uploads.splice(index, 1);
                document.querySelector("#" + this.uid).value = "";
                if (this.uploads.length > 0) {
                    this.uploads.forEach((upload) => {
                        if (this.isMultipleFile()) {
                            values.push({
                                file_name: upload.server_file_name,
                                file_path: upload.server_file_path,
                                file_extension: upload.server_file_extension,
                            });
                        } else {
                            values = {
                                file_name: upload.server_file_name,
                                file_path: upload.server_file_path,
                                file_extension: upload.server_file_extension,
                            };
                        }
                    });
                }
                this.updateValue(values);
                this.$emit("upload:cleared");
            }
        },
        handleFiles(event) {
            this.processFiles(event.target.files);
            this.uploadFiles();
        },
        handleDrop(event) {
            event.preventDefault();
            this.dragging = false;
            this.processFiles(event.dataTransfer.files);
            this.uploadFiles();
        },
        processFiles(selectedFiles) {
            this.internal_error = null;

            let valid_types = this.accept !== undefined ? this.accept.split(/\s*,\s*/) : [];

            if (this.isSingularFile() && !this.isSingularRoundFile()) {
                this.updateValue(null);
                this.uploads = [];
            }

            Array.from(selectedFiles).forEach(file => {
                if (file.size > this.upload_max_filesize_bytes) {
                    this.internal_error = file.name + ' is too large (max: ' + this.formatBytes(this.upload_max_filesize_bytes) + ')';
                    return;
                }

                if (valid_types.length > 0) {
                    if (!valid_types.includes(file.type)) {
                        let file_type_matched = valid_types.some(validType => {
                            if (validType.endsWith("/*")) {
                                let fileTypePrefix = validType.split("/*")[0] + "/";
                                return file.type.startsWith(fileTypePrefix);
                            }
                            return validType === file.type;
                        });

                        if (!file_type_matched) {
                            this.internal_error = file.name + ' is not an accepted file type';
                            return;
                        }
                    }
                }

                this.uploads.push({
                    file: file,
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    url: URL.createObjectURL(file),
                    progress: 0,
                    uploaded: false,
                    server_file_name: null,
                    server_file_path: null,
                    server_file_extension: null,
                });
            });
        },
        uploadFiles() {
            if (!this.uploads.length) {
                return;
            }

            this.$emit("upload:start");
            let endpoint = _config.get(this.endpoint);
            let upload_promises = [];

            for (let upload of this.uploads) {
                if (upload.uploaded) continue;

                let formData = new FormData();
                formData.append("file", upload.file);

                upload_promises.push(_storage.post((endpoint !== null) ? endpoint : this.endpoint, (_response) => {
                    if (_storage.isSuccess(_response)) {
                        let response = _storage.getResponseData(_response);

                        upload.server_file_name = response.file_name;
                        upload.server_file_path = response.file_path;
                        upload.server_file_extension = response.file_extension;

                        upload.uploaded = true;
                    }

                    return _response;
                }, {
                    headers: {
                        "Content-Type": "multipart/form-data"
                    },
                    data: formData,
                    onUploadProgress(e) {
                        upload.progress = Math.round((e.loaded / e.total) * 100);
                    }
                }));
            }

            Promise.all(upload_promises).then((_responses) => {
                if (_responses.length > 0) {
                    let values = null;
                    if (this.isMultipleFile()) {
                        values = [];
                    }
                    _responses.forEach((_response) => {
                        if (_storage.isSuccess(_response)) {
                            let response = _storage.getResponseData(_response);

                            if (this.isMultipleFile()) {
                                values.push(response);
                            } else {
                                values = response;
                            }
                        }
                    });
                    this.updateValue(values);
                }
                this.$emit("upload:end", this);
            });
        },
        updateValue(value) {
            if (this.allow_null || value != null) {
                if (this.isNumber() || this.isNumberWithButtons()) {
                    if (isNaN(value) || value == "") {
                        if (this.isNumberWithButtons()) {
                            value = 0;
                        } else {
                            value = "";
                        }
                    }
                    if (value < this.min) {
                        value = this.min;
                    }
                    if (value > this.max) {
                        value = this.max;
                    }
                    if (this.increment > 1) {
                        let old_value = parseInt(value);
                        let higher_value = Math.ceil(old_value / this.increment) * this.increment;
                        let lower_value = Math.floor(old_value / this.increment) * this.increment;

                        if (higher_value !== old_value && lower_value !== old_value) {

                            let distance_to_higher = Math.abs(higher_value - old_value);
                            let distance_to_lower = Math.abs(lower_value - old_value);

                            let shortest_distance = Math.min(distance_to_higher,distance_to_lower);
                            if (distance_to_higher == shortest_distance) {
                                value = higher_value;
                            }
                            if (distance_to_lower == shortest_distance) {
                                value = lower_value;
                            }
                        }
                    }
                }
                this.$emit("update:modelValue", value);
                if (this.isNumberMaxLength()) {
                    if (value.length >= this.maxlength && this.autotab == true) {
                        this.$nextTick(() => {
                            this.focusNextElement();
                        });
                    }
                }
            }
        },
        updateValueTmp(e) {
            let value = e.target.value;
            this.suggestion_idx = -1;
            this.suggestions_open = value != "";
            this.suggestion_value = value;
            this.$emit("fetch", value);

            if (this.suggestions_f.length == 0 && this.onFetch) {
                this.suggestions_pending = true;
            }
            if (this.suggestion_value != "") {
                let suggestion_found = this.suggestions_f.filter((suggestion) => {
                    return suggestion.text.toLowerCase().trim() == this.suggestion_value.toLowerCase().trim();
                });
                if (suggestion_found.length == 0) {
                    this.updateValue("");
                    // this.triggerValidation();
                }
            }
        },
        allowValue(event) {
            if (this.allow_null || event.target.checked) return true;
            if (this.model.constructor.name == "Array" && this.model.length == 1) event.preventDefault();
        },
        selectValue(suggestion) {
            if (suggestion) {
                this.updateValue(suggestion.value);
                this.triggerValidation();
                this.suggestion_idx = -1;
                this.suggestions_open = false;
                this.suggestion_value = suggestion.text;
                this.$emit("select", suggestion);
            }
        },
        clearValue() {
            this.updateValue("");
            this.triggerValidation();
            this.suggestion_value = "";
        },
        changeSuggestionIdx(idx) {
            this.suggestion_idx = idx;
        },
        scrollSuggestionIntoView() {
            setTimeout(() => {
                let selected_option = this.$el.querySelector(".field__suggestions li.js__hovered");
                if (selected_option) {
                    selected_option.scrollIntoView({behavior: "smooth", block: "center"});
                }
            }, 0);
        },
        onArrowDown() {
            if (this.suggestion_idx < this.suggestions_f.length - 1) {
                this.suggestion_idx = this.suggestion_idx + 1;
            } else {
                this.suggestion_idx = 0;
            }
            this.scrollSuggestionIntoView();
        },
        onArrowUp() {
            if (this.suggestion_idx > 0) {
                this.suggestion_idx = this.suggestion_idx - 1;
            } else {
                this.suggestion_idx = this.suggestions_f.length - 1;
            }
            this.scrollSuggestionIntoView();
        },
        findMatchingSuggestion(value) {
            let matching_suggestions = this.suggestions.filter((suggestion) => {
                let suggestion_value = suggestion.value;
                if (typeof(suggestion_value) == "string") {
                    suggestion_value = suggestion_value.trim();
                }
                let suggestion_text = suggestion.text;
                if (typeof(suggestion_text) == "string") {
                    suggestion_text = suggestion_text.trim();
                }
                if (typeof(value) == "string") {
                    value = value.trim();
                }
                return suggestion_value == value || suggestion_text == value;
            });

            if (matching_suggestions.length > 0) {
                return matching_suggestions[0];
            }
            return null;
        },
        selectSuggestion(e) {
            if (this.suggestion_value != "") {
                if (this.suggestion_idx == -1) {
                    if (this.select_first_suggestion) {
                        if (this.suggestions_f.length > 0) {
                            if (this.suggestions_f[0].value != this.model) {
                                this.selectValue(this.suggestions_f[0]);
                            }
                        }
                    } else {
                        if (this.suggestions_model == true) {
                            let suggestion_found = this.suggestions_f.filter((suggestion) => {
                                return suggestion.text.toLowerCase().trim() == this.suggestion_value.toLowerCase().trim();
                            });
                            if (suggestion_found.length > 0) {
                                if (this.suggestions_open == true && (this.model == null || this.model == "")) {
                                    e.preventDefault();
                                }
                                this.selectValue(suggestion_found[0]);
                            }
                        } else {
                            this.selectValue({
                                value: this.suggestion_value,
                                text: this.suggestion_value,
                            });
                        }
                    }
                } else {
                    if (this.suggestions_open == true && (this.model == null || this.model == "")) {
                        e.preventDefault();
                    }
                    this.selectValue(this.suggestions_f[this.suggestion_idx]);
                }
            }
        },
        focusField(e) {
            if (this.isAutocomplete()) {
                this.$emit("focus", e);
            }
        },
        triggerInnerField(e) {
            if (e.keyCode == 13) {
                e.preventDefault();
                e.target.click();
            }
            if (this.isCheckbox() || this.isSwitch()) {
                if (e.keyCode == 37) {
                    this.model = false;
                }
                if (e.keyCode == 39) {
                    this.model = true;
                }
            }
        },
        labelIsEmpty() {
            return this.label === "";
        },
        hintIsEmpty() {
            return this.hint === "";
        },
        selectLabelIsEmpty() {
            return this.select_label === "";
        },
        errorIsActive() {
            if (this.isAutocomplete() && this.suggestions_open == true) {
                return false;
            }
            if (typeof(this.validationrule) !== "undefined" && this.validationrule.$error === true) {
                return true;
            }
            if (this.internal_error !== null) {
                return true;
            }
            return false;
        },
        minusNumber() {
            if (this.disable_buttons_minus == false) {
                let value = isNaN(parseInt(this.model)) ? (this.min + this.increment) : parseInt(this.model);

                if (value > this.min) {
                    this.updateValue(value - this.increment);
                }
                this.triggerValidation();
            }
        },
        plusNumber() {
            if (this.disable_buttons_plus == false) {
                let value = isNaN(parseInt(this.model)) ? this.min : parseInt(this.model);

                if (value < this.max) {
                    this.updateValue(value + this.increment);
                }
                this.triggerValidation();
            }
        },
        isRequired() {
            if (typeof(this.validationrule) !== "undefined" && typeof(this.validationrule.required) !== "undefined") {
                return true;
            }
            return false;
        },
        isValidType() {
            let valid_types = ["email", "password", "search", "text", "number", "number:maxlength", "number:buttons", "tel", "radio", "radio:round", "checkbox", "checkbox:round", "switch", "select", "select:colors", "select:colors:multi", "select:swatches", "textarea", "autocomplete", "file", "file:round"];
            return valid_types.indexOf(this.type) !== -1;
        },
        isInput() {
            let valid_types = ["email", "search", "text"];
            return valid_types.indexOf(this.type) !== -1;
        },
        isPassword() {
            return this.type == "password";
        },
        isNumber() {
            return this.type == "number";
        },
        isNumberMaxLength() {
            return this.type == "number:maxlength";
        },
        isNumberWithButtons() {
            return this.type == "number:buttons";
        },
        isTel() {
            return this.type == "tel";
        },
        isRadio() {
            return this.type == "radio";
        },
        isRoundRadio() {
            return this.type == "radio:round";
        },
        isCheckbox() {
            return this.type == "checkbox";
        },
        isRoundCheckbox() {
            return this.type == "checkbox:round";
        },
        isSwitch() {
            return this.type == "switch";
        },
        isSelect() {
            return this.type == "select" && this.options.length > 0;
        },
        isSelectColors() {
            return this.type == "select:colors" && this.options.length > 0;
        },
        isMultiSelectColors() {
            return this.type == "select:colors:multi" && this.options.length > 0;
        },
        isSelectSwatches() {
            return this.type == "select:swatches" && this.options.length > 0;
        },
        isTextarea() {
            return this.type == "textarea";
        },
        isAutocomplete() {
            return this.type == "autocomplete";
        },
        isSingularRoundFile() {
            return this.type == "file:round" && this.multiple == false;
        },
        isSingularFile() {
            return (this.type == "file" || this.type == "file:round") && this.multiple == false;
        },
        isMultipleFile() {
            return (this.type == "file" || this.type == "file:round") && this.multiple == true;
        },
        isOptionDisabled(option) {
            if (typeof(option.disabled) !== "undefined" && option.disabled == true) {
                if (option.value == this.model) {
                    this.updateValue(null);
                }
                return true;
            }
            return false;
        },
        isOptionGroup(option) {
            if (typeof(option.group) !== "undefined" && option.group != "no") {
                return true;
            }
            return false;
        },
        hasSubOptions(option) {
            if (typeof(option.options) !== "undefined" && option.options.length > 0) {
                return true;
            }
            return false;
        },
        getSwatchColor(option) {
            if (option.configuration !== undefined && option.configuration.color !== undefined) {
                return option.configuration.color;
            } else if (option.hex !== undefined) {
                return option.hex;
            }
            return null;
        },
        copyImage() {
            this.$emit("copy_image", {
                file: this.uploads[0].file,
                name: this.uploads[0].server_file_name,
                path: this.uploads[0].server_file_path,
                extension: this.uploads[0].server_file_extension,
            });
        }
    },
    computed: {
        uid_name() {
            return this.uid + "_" + this.name;
        },
        error() {
            if (this.errorIsActive() === true) {
                let validation_params = this.validationrule.$errors;
                if (validation_params.length > 0) {
                    for (let i = 0; i < validation_params.length; i++) {
                        let validation_param = validation_params[i];
                        let validation_name = null;
                        if (typeof(validation_param.$params.type) !== "undefined") {
                            if (typeof(validation_param.$validator) !== "undefined") {
                                validation_name = validation_param.$validator;
                            }
                            let validation_type = validation_param.$params.type;
                            let message = "";
                            let verb = "";
                            let params = [this.label.toLowerCase()];
                            for (let param_property in validation_param.$params) {
                                if (validation_param.$params.hasOwnProperty(param_property) && param_property != "type") {
                                    params.push(validation_param.$params[param_property]);
                                }
                            }
                            switch (this.type) {
                                case "file":
                                    verb = "upload";
                                    break;
                                case "radio":
                                case "checkbox":
                                case "switch":
                                case "select":
                                    verb = "select";
                                    break;
                                default:
                                    verb = "enter";
                                    break;
                            }
                            switch (validation_type) {
                                case "required":
                                case "requiredIf":
                                    message = "Please " + verb + " " + (this.multiple ? "some" : ((params[0].match(/^(a|e|i|o|u)/gi) == null) ? "a" : "an")) + " " + params[0];
                                    break;
                                case "minLength":
                                    message = "The " + params[0] + " must contain at least " + params[1] + " characters";
                                    break;
                                case "maxLength":
                                    message = "The " + params[0] + " must contain at most " + params[1] + " characters";
                                    break;
                                default:
                                    message = "The " + params[0] + " isn't valid";
                                    break;
                            }
                            if (typeof(this.validationmsg) !== "undefined") {
                                let message_override = null;
                                if (typeof(this.validationmsg[validation_type]) !== "undefined") {
                                    message_override = this.validationmsg[validation_type];
                                }
                                if (validation_name !== null && typeof(this.validationmsg[validation_name]) !== "undefined") {
                                    message_override = this.validationmsg[validation_name];
                                }
                                if (message_override !== null && message_override != "") {
                                    params.forEach((param, idx) => {
                                        let regex = new RegExp("{[\\s]{0,}" + idx + "[\\s]{0,}}", "g");
                                        let matches = message_override.match(regex);
                                        if (matches != null && matches.length > 0) {
                                            matches.forEach((match) => {
                                                message_override = message_override.replace(match, param);
                                            });
                                        }
                                    });
                                    message = message_override;
                                }
                            }
                            return message;
                        }
                    }
                }
                if (this.internal_error !== null) {
                    return this.internal_error;
                }
            }
            return "";
        },
        suggestions_f() {
            if (this.suggestions.length > 0 && this.suggestion_value != "") {
                if (this.suggestions_fuzzy_match) {
                    return this.suggestions.map((suggestion) => {
                        let pattern = new RegExp("(" + this.suggestion_value.trim().split("").join(" ?") + ")", "i");
                        suggestion.highlighted = suggestion.text.replace(pattern, "<strong>$1</strong>");
                        suggestion.match = suggestion.text.replace(/[\s]/g, "").toLowerCase().trim().includes(
                            this.suggestion_value.replace(/[\s]/g, "").toLowerCase().trim()
                        );
                        return suggestion;
                    });
                } else {
                    return this.suggestions.filter((suggestion) => {
                        let pattern = new RegExp("(" + this.suggestion_value.trim().split("").join(" ?") + ")", "i");
                        suggestion.highlighted = suggestion.text.replace(pattern, "<strong>$1</strong>");
                        return (suggestion.text.replace(/[\s]/g, "").toLowerCase().trim().indexOf(this.suggestion_value.replace(/[\s]/g, "").toLowerCase().trim()) !== -1);
                    });
                }
            }
            return [];
        },
        suggestions_available() {
            return this.suggestions_open == true &&
                this.suggestions_f.length > 0 &&
                this.suggestion_value != "" &&
                this.suggestions_pending == false;
        },
        suggestions_unavailable() {
            let suggestions_unavailable = this.suggestions_open == true &&
            this.suggestions_f.length == 0 &&
            this.suggestion_value != "" &&
            this.suggestions_pending == false;
            if (suggestions_unavailable) {
                this.$emit("nosuggestions", this.suggestion_value);
                if (!this.onNosuggestions) {
                    return true;
                }
            } else {
                if (this.suggestions_open == true || this.suggestion_value == "") {
                    this.$emit("nosuggestions", null);
                }
            }
            return false;
        },
        suggestions_fetching() {
            return this.suggestions_open == true &&
                this.suggestion_value != "" &&
                this.suggestions_pending == true;
        }
    },
    template: `
    <div class="field__wrapper" :class="[isNumberWithButtons() ? 'field__wrapper--number' : '']" v-if="isValidType()">
        <div v-if="!hintIsEmpty()" class="field__hint"><span class="icon"></span><span class="text" v-html="hint"></span></div>
        <label v-if="!labelIsEmpty() && (isInput() || isPassword() || isMultipleFile() || (isSingularFile() && !isSingularRoundFile()) || isTel() || isNumber() || isNumberWithButtons() || isSelect() || isSelectColors() || isMultiSelectColors() || isSelectSwatches() || isTextarea() || isAutocomplete())" :class="{ is_required: isRequired() }" class="field__title" v-html="label" :for="uid"></label>
        <input v-if="isInput()" :aria-label="label" :name="uid_name" :type="type" :placeholder="placeholder" :pattern="pattern" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid" />
        <label class="field__inner" v-if="isPassword()">
            <div class="password--switch" @click="password_shown = !password_shown">{{ password_shown ? "Hide" : "Show" }}</div>
            <input :aria-label="label" :name="uid_name" :type="password_shown ? 'text' : 'password'" :placeholder="placeholder" :autocomplete="autocomplete" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid" />
        </label>
        <label class="field file relative" :class="{ top: isSingularRoundFile(), has_file: uploads && uploads.length > 0, dragging: dragging == true }" v-if="isSingularFile()" tabindex="0" @keydown="triggerInnerField" @dragover.prevent @dragenter.prevent="dragging = true" @dragleave="dragging = false" @drop="handleDrop">
            <span class="text" v-html="label" v-if="!labelIsEmpty() && isSingularRoundFile()"></span>
            <template v-if="!isSingularRoundFile()">
                <template v-if="model">{{ model.file_name }}</template>
                <template v-else-if="uploads && uploads.length > 0">Uploading file... {{ uploads[0].progress }}%</template>
                <template v-else>No file chosen <small>(max: {{ formatBytes(upload_max_filesize_bytes) }})</small></template>
            </template>
            <input :aria-label="label" :name="uid_name" type="file" :accept="accept" :placeholder="placeholder" :disabled="disabled" :pattern="pattern" @change="handleFiles" @blur="triggerValidation" :id="uid" tabindex="-1" />
            <div v-if="uploads && uploads.length > 0 && isSingularRoundFile() && preview" class="preview file round">
                <img class="preview file" v-if="uploads[0].type.includes('image')" :src="uploads[0].url" />
            </div>
            <div v-if="uploads && uploads.length > 0 && !isSingularRoundFile()" class="upload_progress_line" :style="{'--progress' : uploads[0].progress + '%'}"></div>
        </label>
        <div v-if="uploads && uploads.length > 0 && isSingularRoundFile()" class="flex flex-col justify-center">
            <button class="hidden:1t2e mx-auto block btn:xs bg-transparent mb-1" @click="removeFile(0)">Delete</button>
            <a class="hidden:1t2e block text-xs" @click="copyImage()">Copy to {{ copy_to }}</a>
            <a @click="removeFile(0)" class="hidden:3 block text-xs" style="padding-top: 15px;">Delete</a>
        </div>
        <label class="field file multiple relative" :class="{ has_file: uploads && uploads.length > 0, dragging: dragging == true }" v-if="isMultipleFile()" tabindex="0" @keydown="triggerInnerField">
            <div class="drop-area" @dragover.prevent @dragenter.prevent="dragging = true" @dragleave="dragging = false" @drop="handleDrop">
                <template v-if="!dragging"><p><u>Click to upload</u> or drag and drop<br/><small>Maximum file size: {{ formatBytes(upload_max_filesize_bytes) }}</small></p></template>
                <template v-else>Drop the files to upload</template>
            </div>
            <input :aria-label="label" :name="uid_name" type="file" multiple :accept="accept" :placeholder="placeholder" :disabled="disabled" :pattern="pattern" @change="handleFiles" @blur="triggerValidation" :id="uid" tabindex="-1" />
        </label>
        <div v-if="(isMultipleFile() || (isSingularFile() && !isSingularRoundFile() && model)) && uploads.length > 0 && preview" class="previews">
            <div v-for="(upload, index) in uploads" :key="index" class="preview">
                <div class="visual" v-if="upload.type.includes('image') || upload.type.includes('video')">
                    <img v-if="upload.type.includes('image')" :src="upload.url" class="preview-img">
                    <video v-else-if="upload.type.includes('video')" controls class="preview-video">
                        <source :src="upload.url" :type="upload.type">
                    </video>
                </div>
                <div v-if="isMultipleFile()" class="textual">
                    <div class="upload_name">{{ upload.name }}</div>
                    <div class="upload_progress">
                        <div class="upload_progress_line" :style="{'--progress' : upload.progress + '%'}"></div>
                        <span>{{ upload.progress + '%' }}</span>
                    </div>
                </div>
                <div @click="removeFile(index)" class="preview__dismiss"></div>
            </div>
        </div>
        <div class="field__inner" v-if="isNumberWithButtons()">
            <div class="minus" @click="minusNumber()" :disabled="disable_buttons_minus === true"></div>
            <input v-if="isNumberWithButtons()" :aria-label="label" :name="uid_name" type="text" :placeholder="placeholder" pattern="\\d*" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid" />
            <div class="plus" @click="plusNumber()" :disabled="disable_buttons_plus === true"></div>
        </div>
        <input v-if="isNumber()" :aria-label="label" :name="uid_name" type="text" :placeholder="placeholder" pattern="\\d*" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid" />
        <input v-if="isNumberMaxLength()" :aria-label="label" :name="uid_name" type="text" :placeholder="placeholder" pattern="\\d*" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :maxlength="maxlength" :id="uid" />
        <input v-if="isTel()" :aria-label="label" :name="uid_name" type="tel" :placeholder="placeholder" pattern="\\d*" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid" />
        <label v-if="isRadio()" class="radio" tabindex="0" @keydown="triggerInnerField"><input :aria-label="label" :name="uid_name" type="radio" :disabled="disabled" :value="value" v-model="model" @blur="triggerValidation" tabindex="-1" /><span class="text" v-html="valuelabel" v-if="valuelabel"></span></label>
        <label v-if="isRoundRadio()" class="radio top" tabindex="0" @keydown="triggerInnerField"><input :aria-label="label" :name="uid_name" type="radio" :disabled="disabled" :value="value" v-model="model" @blur="triggerValidation" tabindex="-1" class="round" /><span class="text" v-html="valuelabel" v-if="valuelabel"></span></label>
        <label v-if="isCheckbox()" class="checkbox" tabindex="0" @keydown="triggerInnerField"><input :aria-label="label" :name="uid_name" :value="value" type="checkbox" :disabled="disabled" v-model="model" @click="allowValue($event)" @blur="triggerValidation" tabindex="-1" /><span class="text" v-html="valuelabel" v-if="valuelabel"></span></label>
        <label v-if="isRoundCheckbox()" class="checkbox top" tabindex="0" @keydown="triggerInnerField"><input :aria-label="label" :name="uid_name" type="checkbox" :disabled="disabled" v-model="model" @click="allowValue($event)" @blur="triggerValidation" tabindex="-1" class="round" /><span class="text" v-html="valuelabel" v-if="valuelabel"></span></label>
        <label v-if="isSwitch()" class="switch" tabindex="0" @keydown="triggerInnerField"><input :aria-label="label" :name="uid_name" type="checkbox" :disabled="disabled" v-model="model" @blur="triggerValidation" tabindex="-1" /><span class="text" v-html="valuelabel" v-if="valuelabel"></span></label>
        <select v-if="isSelect()" :aria-label="label" :name="uid_name" :autocomplete="autocomplete" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @change="updateValue($event.target.value)" @blur="triggerValidation" :id="uid">
        <option value="" selected :disabled="isRequired()">Select {{ selectLabelIsEmpty() ? label : select_label }}</option>
        <template v-for="option in options">
            <template v-if="isOptionGroup(option)">
                <optgroup :label="option.group">
                    <option v-for="option in option.options" :value="option.value">{{ option.text }}</option>
                </optgroup>
            </template>
            <template v-if="!isOptionGroup(option)">
                <template v-if="hasSubOptions(option)">
                    <option v-for="option in option.options" :value="option.value">{{ option.text }}</option>
                </template>
                <template v-if="!hasSubOptions(option)">
                    <option :value="option.value">{{ option.text }}</option>
                </template>
            </template>
        </template>
        </select>
        <div v-if="isSelectColors()" class="field__collection">
        <div v-for="(option, option_idx) in options">
        <input :aria-label="label" :name="uid_name" type="radio" :disabled="isOptionDisabled(option)" v-model="model" :value="option.value" :checked="model == option.value" @blur="triggerValidation" :id="(option_idx == 0) ? uid : uid + '_' + option_idx" class="radio color" :style="{ '--input_checkbox_radio_color': getSwatchColor(option) }" />
        </div>
        </div>
        <div v-if="isMultiSelectColors()" class="field__collection">
        <div v-for="(option, option_idx) in options">
        <input :aria-label="option.label" :name="uid_name" type="checkbox" :disabled="isOptionDisabled(option)" :value="option" v-model="model" @click="allowValue($event)" @blur="triggerValidation" :id="(option_idx == 0) ? uid : uid + '_' + option_idx" class="radio color" :style="{ '--input_checkbox_radio_color': getSwatchColor(option) }" />
        </div>
        </div>
        <div v-if="isSelectSwatches()" class="field__collection">
        <input v-for="(option, option_idx) in options" :aria-label="label" :name="uid_name" type="radio" :disabled="isOptionDisabled(option)" :value="option.value" :checked="model == option.value" @click="updateValue($event.target.value)" @blur="triggerValidation" :id="(option_idx == 0) ? uid : uid + '_' + option_idx" class="radio swatch" :data-label="option.label" />
        </div>
        <textarea v-if="isTextarea()" :aria-label="label" :name="uid_name" :placeholder="placeholder" :readonly="readonly" :autofocus="autofocus" :disabled="disabled" :value="model" @input="updateValue($event.target.value)" @blur="triggerValidation" :id="uid"></textarea>
        <div v-if="isAutocomplete()" class="field__autocomplete">
        <input :name="uid_name" type="text" :placeholder="placeholder" autocomplete="off" :disabled="disabled" :value="suggestion_value" @input="updateValueTmp" @keydown.down="onArrowDown" @keydown.up="onArrowUp" @keydown.enter="selectSuggestion" @blur="selectSuggestion" @focus="focusField" :id="uid" />
        <div v-if="suggestions_available" class="field__suggestions">
            <ul>
                <li v-for="(suggestion, idx) in suggestions_f" @click.stop="selectValue(suggestion)" :class="{ 'js__hovered': idx == suggestion_idx }" @mouseover="changeSuggestionIdx(idx)">
                    <slot name="suggestion" :suggestion="suggestion"><span v-html="suggestion.highlighted"></span></slot>
                </li>
            </ul>
        </div>
        <div v-if="suggestions_unavailable" class="field__suggestions"><span>No results</span></div>
        <div v-if="suggestions_fetching" class="field__suggestions"><span>Loading...</span></div>
        </div>
        <span v-if="errorIsActive()" class="field__message--error" v-html="error"></span>
    </div>
    `,
    mounted() {
        if (this.isAutocomplete()) {
            let input = this.$el.querySelector("input");
            input.addEventListener("click", (e) => {
                e.stopPropagation();
            });
            input.addEventListener("focus", () => {
                if (this.suggestions_f.length > 0 && this.suggestion_value != "") {
                    this.suggestions_open = true;
                }
            });
            input.addEventListener("blur", () => {
                if (this.suggestion_value == "") {
                    this.clearValue();
                }
            });
            document.addEventListener("click", () => {
                this.suggestions_open = false;
            });
        }
    }
};

export default _input;