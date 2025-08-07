import _config from "@/utilities/config.js";
_config.init();

import { useVuelidate } from "@vuelidate/core";

import _utilities from "@/utilities/utilities.js";
import _product_utilities from "@/utilities/products.js";
import { _storage } from "@/utilities/api.js";

import { helpers as _validation_helpers } from "@vuelidate/validators";
import _validation from "@/utilities/validation.js";

let fields = {
    create_address: {
        name: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        address_line_1: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        address_line_2: {},
        city: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        county: {},
        postcode: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        country: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        is_default_billing: {
            default: false,
        },
        is_default_shipping: {
            default: false,
        },
    },
    update_address: {
        id: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        name: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        address_line_1: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        address_line_2: {},
        city: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        county: {},
        postcode: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        country: {
            validations: {
                rules: {
                    required: _validation.rules.required
                }
            }
        },
        is_default_shipping: {
            default: false,
        },
        is_default_billing: {
            default: false,
        },
    },
};

let _user_addresses = {
    name: "c-user_addresses",
    setup () {
        const v$ = useVuelidate({ $stopPropagation: true });
        return { v$ };
    },
    emits: [
        "message:hide",
        "message:success",
        "message:error",
        "select",
        "fetch",
    ],
    props: {
        initial_selected_address_id: {
            default: null
        },
    },
    watch: {
        initial_selected_address_id: {
            handler(new_val, old_val) {
                if (old_val !== undefined) {
                    this.selected_address_id = new_val;
                }
            },
            immediate: true
        }
    },
    data() {
        return {
            is_logged_in: _config.get("user.is_logged_in"),
            is_loading: false,
            is_success_message_shown: false,
            success_message: "",
            is_error_message_shown: false,
            error_message: "",
            stage: "index",
            selected_address_id: null,
            selected_for_deletion_address_idx: null,
            deletion_confirmation: false,
            addresses: [],
            addresses_fetched: false,
            forms: {
                create_address: {
                    ref: null,
                    ref_name: "create_address",
                    action: "api.user.addresses.create",
                    method: "post",
                    field_values: _validation.createFieldsData(fields.create_address),
                    field_storage: _validation.createFieldsStorage(fields.create_address),
                    validation_rules: _validation.createFieldsValidationRules(fields.create_address),
                    validation_messages: _validation.createFieldsValidationMessages(fields.create_address),
                },
                update_address: {
                    ref: null,
                    ref_name: "update_address",
                    action: "api.user.addresses.update",
                    method: "post",
                    field_values: _validation.createFieldsData(fields.update_address),
                    field_storage: _validation.createFieldsStorage(fields.update_address),
                    validation_rules: _validation.createFieldsValidationRules(fields.update_address),
                    validation_messages: _validation.createFieldsValidationMessages(fields.update_address),
                },
            }
        };
    },
    methods: {
        clearMessages() {
            this.is_success_message_shown = false;
            this.is_error_message_shown = false;
            this.$emit("message:hide");
        },
        showSuccessMessage(message) {
            this.success_message = message;
            this.is_success_message_shown = true;
            this.$emit("message:success", { message: this.success_message });
        },
        showErrorMessage(message) {
            this.error_message = message;
            this.is_error_message_shown = true;
            this.$emit("message:error", { message: this.error_message });
        },
        indexAddress() {
            this.stage = "index";
        },
        setCreateAddressRef(ref) {
            this.forms.create_address.ref = ref;
        },
        createAddress() {
            this.stage = "create";
        },
        cancelCreateAddress() {
            this.indexAddress();
            if (this.forms.create_address.ref) {
                setTimeout(() => {
                    this.forms.create_address.ref.clearFields(true);
                }, 450);
            }
        },
        successCreateAddress() {
            this.$emit('fetch');
            this.fetchAddresses();
            this.indexAddress();
            if (this.forms.create_address.ref) {
                setTimeout(() => {
                    this.forms.create_address.ref.clearFields(true);
                }, 450);
            }
        },
        setUpdateAddressRef(ref) {
            this.forms.update_address.ref = ref;
        },
        updateAddress(idx) {
            if (this.addresses[idx] !== undefined) {
                const address = this.addresses[idx];

                Object.keys(address).forEach(address_property => {
                    if (this.forms.update_address.field_values[address_property] !== undefined) {
                        this.forms.update_address.field_values[address_property] = address[address_property];
                    }
                });

                this.stage = "update";
            }
        },
        cancelUpdateAddress() {
            this.indexAddress();
            if (this.forms.update_address.ref) {
                setTimeout(() => {
                    this.forms.update_address.ref.clearFields(true);
                }, 450);
            }
        },
        successUpdateAddress() {
            this.$emit('fetch');
            this.fetchAddresses();
            this.indexAddress();
            if (this.forms.update_address.ref) {
                setTimeout(() => {
                    this.forms.update_address.ref.clearFields(true);
                }, 450);
            }
        },
        attemptDeleteAddress(idx) {
            if (this.addresses[idx] !== undefined) {
                this.selected_for_deletion_address_idx = idx;
                this.openDeletionConfirmation();
            }
        },
        openDeletionConfirmation() {
            this.deletion_confirmation = true;
        },
        closeDeletionConfirmation() {
            this.deletion_confirmation = false;
        },
        deleteAddress() {
            if (this.addresses[this.selected_for_deletion_address_idx] !== undefined) {
                const address = this.addresses[this.selected_for_deletion_address_idx];

                _storage.post(_config.get("api.user.addresses.delete"), (_response) => {
                    if (_storage.isSuccess(_response)) {
                        this.$emit('fetch');
                        this.fetchAddresses();
                    }
                }, {
                    data: {
                        id: address.id
                    }
                });
            }
        },
        fetchAddresses() {
            if (this.is_logged_in) {
                _storage.get(_config.get("api.user.addresses.index"), (_response) => {
                    this.addresses = [];
                    if (_storage.isSuccess(_response)) {
                        const response = _storage.getResponseData(_response);
                        if (response.addresses.length > 0) {
                            this.addresses = response.addresses;
                        }
                        this.addresses_fetched = true;
                    }
                    if (_storage.isError(_response)) {
                    }
                });
            } else {
                this.addresses = [];
            }
        },
        selectAddress(id) {
            if (id != this.selected_address_id) {
                this.selected_address_id = id;
            } else {
                this.selected_address_id = null;
            }
            this.$emit('select', this.selected_address_id);
        }
    },
    computed: {
    },
    created() {
        this.fetchAddresses();
    },
    template: `
    <slot

    :v$="v$"
    :is_loading="is_loading"
    :is_logged_in="is_logged_in"

    :is_success_message_shown="is_success_message_shown"
    :success_message="success_message"
    :is_error_message_shown="is_error_message_shown"
    :error_message="error_message"

    :stage="stage"
    :deletion_confirmation="deletion_confirmation"
    :addresses_fetched="addresses_fetched"
    :addresses="addresses"
    :forms="forms"

    :fetchAddresses="fetchAddresses"
    :setCreateAddressRef="setCreateAddressRef"
    :createAddress="createAddress"
    :cancelCreateAddress="cancelCreateAddress"
    :successCreateAddress="successCreateAddress"
    :setUpdateAddressRef="setUpdateAddressRef"
    :updateAddress="updateAddress"
    :cancelUpdateAddress="cancelUpdateAddress"
    :successUpdateAddress="successUpdateAddress"
    :indexAddress="indexAddress"

    :attemptDeleteAddress="attemptDeleteAddress"
    :deleteAddress="deleteAddress"
    :openDeletionConfirmation="openDeletionConfirmation"
    :closeDeletionConfirmation="closeDeletionConfirmation"

    :selected_address_id="selected_address_id"
    :selectAddress="selectAddress"
    ></slot>
    `
};

export default _user_addresses;