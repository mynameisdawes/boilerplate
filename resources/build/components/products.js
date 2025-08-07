import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

import _ from 'lodash';

let _products = {
    name: "c-products",
    emits: [
        "loaded:products"
    ],
    props: {
        filter: {
            type: Boolean,
            default: _config.get('shop.filters.enabled')
        },
        filters: {
            type: Array,
            default: []
        },
        ids: {
            type: Array,
            default() {
                return [];
            }
        },
        paginate: {
            type: Boolean,
            default: _config.get("shop.pagination.enabled")
        },
        per_pages: {
            type: Array,
            default() {
                return _config.get("shop.pagination.per_pages");
            }
        }
    },
    data() {
        return {
            hide_pricing: _config.get("shop.hide_pricing"),
            is_loading: false,
            products_fetched: false,
            products: [],
            pagination: {},
        };
    },
    methods: {
        changePaginationGetProducts(data) {
            if (this.paginate) {
                this.getProducts(data);
            }
        },
        getProducts(data) {
            this.is_loading = true;
            this.products_fetched = false;

            let payload = {};
            if (typeof(data) !== "undefined") {
                payload.data = data;
            } else {
                payload.data = {};
                if (this.paginate === true) {
                    if (this.pagination.page !== undefined) {
                        payload.data.page = this.pagination.page;
                    }
                    if (this.pagination.per_page !== undefined) {
                        payload.data.per_page = this.pagination.per_page;
                    }
                }
            }

            if (this.filters.length > 0) {
                payload.data.filters = this.filters;
            }

            if (_config.get("shop.product_type")) {
                if (payload.data.filters === undefined) {
                    payload.data.filters = [];
                }
                const product_type_filter = {
                    attribute_name: "product_type",
                    value: _config.get("shop.product_type"),
                };
                payload.data.filters.push(product_type_filter);
            }

            if (this.paginate === true) {
                payload.data.paginate = this.paginate;
            }

            if (this.ids.length > 0) {
                payload.data.ids = this.ids;
            }

            return _storage.post(_config.get("api.products.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);
                    if (_storage.hasPaginationData(response.products)) {
                        let paginated_products = _storage.getPaginationData(response.products);
                        this.products = paginated_products.data;
                        delete paginated_products.data;
                        this.pagination = paginated_products;
                    } else {
                        this.products = response.products;
                    }
                    this.$emit('loaded:products', this.products);
                    this.products_fetched = true;
                }
                this.is_loading = false;
            }, payload);
        },
        formatPrice(price) {
            return new Intl.NumberFormat("en-GB", { style: "currency", currency: "GBP" }).format(price);
        }
    },
    watch: {
        filters: {
            handler(new_val, old_val) {
                if (this.filter && old_val !== undefined && !_.isEqual(new_val, old_val)) {
                    this.getProducts();
                }
            },
            deep: true,
            immediate: true
        }
    },
    mounted() {
        if (!this.filter && !this.paginate) {
            this.getProducts();
        }
    },
    template: `
    <slot
        :is_loading="is_loading"
        :per_pages="per_pages"
        :paginate="paginate"

        :products_fetched="products_fetched"
        :products="products"
        :pagination="pagination"
        :filters="filters"

        :changePaginationGetProducts="changePaginationGetProducts"
        :getProducts="getProducts"
        :formatPrice="formatPrice"
    ></slot>
    `
};

export default _products;