import _config from "@/utilities/config.js";
_config.init();

import { _api, _storage } from "@/utilities/api.js";

let _onecrm_orders = {
    name: "c-onecrm_orders",
    props: {
    },
    data() {
        return {
            data_fetched: false,
            is_loading: false,
            orders: []
        };
    },
    methods: {
        getOrders() {
            this.is_loading = true;
            _storage.post(_config.get("api.onecrm.orders.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    this.orders = response.orders;
                    this.is_loading = false;
                    this.data_fetched = true;
                }
            });
        }
    },
    created() {
        this.is_loading = true;
        this.getOrders();
    },
    mounted() {
    },
    template: `
    <slot
    :data_fetched="data_fetched"
    :is_loading="is_loading"
    :orders="orders"
    ></slot>
    `
};

export default _onecrm_orders;