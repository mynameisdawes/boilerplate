import _config from "@/utilities/config.js";
_config.init();

import { _api, _storage } from "@/utilities/api.js";

let _onecrm_quote = {
    name: "c-onecrm_quote",
    props: {
        id: {
            default: null
        },
    },
    data() {
        return {
            data_fetched: false,
            is_loading: false,
            quote: null,
            proofs: {
                confirmed: false
            },
            already_converted: false
        };
    },
    methods: {
        getQuote() {
            this.is_loading = true;

            _storage.post(_config.get("api.checkout_quote.show") + "/" + this.id + window.location.search, (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.quote = response.quote;
                    this.setStorage();
                    this.is_loading = false;
                    this.data_fetched = true;
                } else {
                    let response = _storage.getResponseData(_response);
                    if (response.already_converted == true) {
                        this.already_converted = true;
                    }
                    this.is_loading = false;
                }
            });
        },
        setStorage() {
            let storage_data = {
                first_name: this.quote.first_name,
                last_name: this.quote.last_name,
                email: this.quote.email,
                phone: this.quote.phone,
            };
            window.localStorage.setItem(`${this.id}_checkout--fields`, JSON.stringify(storage_data));
            window.localStorage.setItem(`${this.id}_checkout--timeout`, Math.floor(Date.now() / 1000));
        }
    },
    created() {
        this.is_loading = true;
        this.getQuote();
    },
    template: `
    <slot
    :data_fetched="data_fetched"
    :is_loading="is_loading"
    :quote="quote"
    :proofs="proofs"
    :already_converted="already_converted"
    ></slot>
    `
};

export default _onecrm_quote;