import _config from "@/utilities/config.js";
_config.init();

import { _api, _storage } from "@/utilities/api.js";

let _onecrm_order = {
    name: "c-onecrm_order",
    props: {
        id: {
            default: null
        },
    },
    data() {
        return {
            data_fetched: false,
            is_loading: false,
            order: null
        };
    },
    methods: {
        getOrder() {
            this.is_loading = true;
            _storage.post(_config.get("api.onecrm.orders.show") + "/" + this.id, (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.order = response.order;
                    this.is_loading = false;
                    this.data_fetched = true;
                }
            });
        }
    },
    created() {
        this.is_loading = true;
        this.getOrder();
    },
    template: `
    <slot
    :data_fetched="data_fetched"
    :is_loading="is_loading"
    :order="order"
    ></slot>
    `
};

export default _onecrm_order;