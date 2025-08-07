import _config from "../utilities/config.js";
_config.init();

import { CookieStorage } from "cookie-storage";
const cookieStorage = new CookieStorage();

import _utilities from "../utilities/utilities.js";
import _validation from "../utilities/validation.js";

let fields = {
    age_gate: {
        dob_day: {},
        dob_month: {},
        dob_year: {},
    },
};

let _age_gate = {
    name: "c-age_gate",
    emits: ["verify"],
    data() {
        return {
            year: new Date().getFullYear(),
            age_verified: false,
            forms: {
                age_gate: {
                    field_values: _validation.createFieldsData(fields.age_gate),
                    field_storage: _validation.createFieldsStorage(fields.age_gate),
                    validation_rules: _validation.createFieldsValidationRules(fields.age_gate),
                    validation_messages: _validation.createFieldsValidationMessages(fields.age_gate),
                },
            },
        };
    },
    computed: {
        valid_year() {
            const enteredYear = this.forms.age_gate.field_values.dob_year;
            return enteredYear.length === 4 && enteredYear <= this.year - 18;
        },
        valid_month() {
            const enteredMonth = this.forms.age_gate.field_values.dob_month;
            return enteredMonth.length === 2 && enteredMonth <= 12;
        },
        valid_day() {
            const field_values = this.forms.age_gate.field_values;
            const enteredDay = field_values.dob_day;
            const enteredYear = this.valid_year ? field_values.dob_year : this.year;
            const enteredMonth = this.valid_month ? field_values.dob_month : 0;
            const daysInMonth = new Date(enteredYear, enteredMonth, 0).getDate();
            return enteredDay.length === 2 && enteredDay <= daysInMonth;
        },
        age_entered() {
            const field_values = this.forms.age_gate.field_values;
            const fieldsFilled = field_values.dob_day.length === 2 && field_values.dob_month.length === 2 && field_values.dob_year.length === 4;
            return fieldsFilled && this.valid_year && this.valid_month && this.valid_day;
        },
    },
    methods: {
        checkAge() {
            if (this.age_entered) {
                const field_values = this.forms.age_gate.field_values;
                const birthday = new Date(field_values.dob_year, field_values.dob_month - 1, field_values.dob_day);
                const isEighteen = (Date.now() - birthday) / 31557600000 > 18;
                if (isEighteen) {
                    this.age_verified = true;
                    this.$emit("verify", {
                        event: "check",
                        verified: true,
                    });
                    cookieStorage.setItem("age_verified", _utilities.timestamp());
                } else {
                    this.age_verified = false;
                    this.$emit("verify", {
                        event: "check",
                        verified: true,
                    });
                }
            }
        },
    },
    created() {
        if (cookieStorage.getItem("age_verified")) {
            this.age_verified = true;
            this.$emit("verify", {
                event: "load",
                verified: true,
            });
        } else {
            this.age_verified = false;
            this.$emit("verify", {
                event: "load",
                verified: false,
            });
        }
    },
    template: `
    <slot
    :age_verified="age_verified"
    :forms="forms"
    :age_entered="age_entered"
    :checkAge="checkAge"
    ></slot>
    `,
};

export default _age_gate;