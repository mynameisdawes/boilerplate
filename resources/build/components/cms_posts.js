import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _cms_posts = {
    name: "c-cms_posts",
    props: {
        paginate: {
            type: Boolean,
            default: true
        },
        per_pages: {
            type: Array,
            default() {
                return [4, 8, 12, 24];
            }
        },
        category_ids: {
            type: Array,
            default() {
                return [];
            }
        },
        tag_ids: {
            type: Array,
            default() {
                return [];
            }
        }
    },
    data() {
        return {
            is_loading: false,
            posts_fetched: false,
            posts: [],
            pagination: {},
        };
    },
    methods: {
        getPosts(data) {
            this.is_loading = true;

            let timeout = 0;
            let payload = {};
            if (typeof(data) !== "undefined") {
                if (this.posts_fetched) {
                    timeout = 600;
                }
                payload.data = data;
                if (this.paginate === true) {
                    payload.data.paginate = true;
                }

                if (this.category_ids.length > 0) {
                    payload.data.category_ids = this.category_ids;
                }

                if (this.tag_ids.length > 0) {
                    payload.data.tag_ids = this.tag_ids;
                }
            }

            return _storage.post(_config.get("api.posts.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);

                    window.scrollTo({top: 0, behavior: 'smooth'});

                    if (_storage.hasPaginationData(response.posts)) {
                        setTimeout(() => {
                            let paginated_posts = _storage.getPaginationData(response.posts);
                            this.posts = paginated_posts.data;
                            delete paginated_posts.data;
                            this.pagination = paginated_posts;

                            this.posts_fetched = true;
                            this.is_loading = false;
                        }, timeout);
                    } else {
                        this.posts = response.posts;

                        this.posts_fetched = true;
                        this.is_loading = false;
                    }
                }
            }, payload);
        },
    },
    created() {
        this.is_loading = true;

        if (this.paginate == false && this.posts != null && this.posts.length == 0) {
            this.getPosts();
        }
    },
    template: `
    <slot
        :is_loading="is_loading"
        :per_pages="per_pages"
        :paginate="paginate"

        :posts_fetched="posts_fetched"
        :posts="posts"
        :pagination="pagination"

        :getPosts="getPosts"
    ></slot>
    `
};

export default _cms_posts;
