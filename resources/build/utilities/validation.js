import {
    required,
    minLength,
    maxLength,
    sameAs,
    email,
    helpers,
    minValue
} from "@vuelidate/validators";

let _validation = {
    rules: {
        required,
        minLength,
        maxLength,
        sameAs,
        email,
        minValue
    },
    helpers: helpers,
    createFieldsData(fields) {
        let data = {};
        for (let property in fields) {
            if (fields.hasOwnProperty(property)) {
                let field = fields[property];
                if (typeof(field.$each) !== "undefined") {
                    data[property] = [];
                    data[property].push(this.createFieldsData(field.$each));
                } else {
                    data[property] = (typeof(field.default) !== "undefined") ? field.default : "";
                }
            }
        }
        return data;
    },
    createFieldsStorage(fields) {
        let data = {};
        for (let property in fields) {
            if (fields.hasOwnProperty(property)) {
                let field = fields[property];
                if (typeof(field.$each) !== "undefined") {
                    data[property] = [];
                    data[property].push(this.createFieldsStorage(field.$each));
                } else {
                    data[property] = (typeof(field.storage) !== "undefined" && field.storage == true) ? true : false;
                }
            }
        }
        return data;
    },
    createFieldsValidationRules(fields) {
        let data = {};
        for (let property in fields) {
            if (fields.hasOwnProperty(property)) {
                let field = fields[property];
                if (typeof(field.$each) !== "undefined") {
                    data[property] = {
                        $each: this.createFieldsValidationRules(field.$each),
                    };
                } else {
                    if (typeof(field.validations) !== "undefined" && typeof(field.validations.rules) !== "undefined") {
                        data[property] = field.validations.rules;
                    }
                }
            }
        }
        return data;
    },
    createFieldsValidationMessages(fields) {
        let data = {};
        for (let property in fields) {
            if (fields.hasOwnProperty(property)) {
                let field = fields[property];
                if (typeof(field.$each) !== "undefined") {
                    data[property] = this.createFieldsValidationMessages(field.$each);
                } else {
                    if (typeof(field.validations) !== "undefined" && typeof(field.validations.messages) !== "undefined") {
                        data[property] = field.validations.messages;
                    }
                }
            }
        }
        return data;
    }
};

export default _validation;