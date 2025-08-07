import _config from "@/utilities/config.js";
_config.init();

import { _storage } from "@/utilities/api.js";

let _cms_cards = {
    name: "c-cms_cards",
    emits: [
    ],
    props: {
        initial_cards: {
            type: Array,
            default() {
                return [];
            }
        },
        paginate: {
            type: Boolean,
            default: true
        },
        per_page: {
            type: Number,
            default: 1,
        },
        initial_page: {
            type: Number,
            default: 1,
        },
        last_page: {
            type: Number,
            required: true,
        },
    },
    data() {
        return {
            is_loading: false,
            is_first_time: true,
            card_paging_observer: null,
            card_focus_observer: null,
            card_list_top_ref: null,
            card_list_bottom_ref: null,
            card_list_ref: null,
            card_scroll_section_ref: null,
            card_refs: [],
            cards_updating: false,
            pre_cards: [ {},{},{},{},{},{},{},{},{},{} ],
            loaded_pages: [],
            pages: {},
            will_prepend: false,
            last_scroll_top: null,
            last_scroll_height: null,
            focussed_id: null,
            clicked_id: null,
            will_scroll: false,
            has_scrolled: false,
            has_scrolled_instant: false,
        };
    },
    watch: {
        cards: {
            handler() {
                this.cards_updating = true;
            },
            deep: true
        }
    },
    computed: {
        cards() {
            return [...this.loaded_pages].sort((a,b) => a - b).flatMap(p => this.pages[p] || []);
        },
    },
    methods: {
        async onPageReveal() {
            this.onArrive();
        },
        onLoad() {
            if (document.prerendering === undefined || (document.prerendering !== undefined && document.prerendering === false)) {
                this.onArrive();
            }
        },
        onArrive() {
            if (this.is_first_time === true && this.cards.length > 0) {
                const card_id = sessionStorage.getItem('card_id');

                if (this.paginate === true) {
                    if (card_id !== null) {
                        this.observeCardsPaging();
                    } else {
                        if (this.card_list_ref) {
                            const card_list_inner_ref = this.card_list_ref.querySelector('.card-list-inner');

                            if (card_list_inner_ref) {
                                card_list_inner_ref.ontransitionend = () => {
                                    this.observeCardsPaging();
                                };
                            }
                        }
                    }
                } else {
                    this.fetchCards();
                }

                if (card_id !== null) {
                    const card_idx = this.card_refs.findIndex(card => card.id == card_id);

                    if (card_idx > -1 && this.card_refs[card_idx] !== undefined) {
                        this.clicked_id = this.card_refs[card_idx].id;
                        this.focussed_id = this.card_refs[card_idx].id;
                    }

                    this.$nextTick(() => {
                        if (card_idx > -1 && this.card_refs[card_idx] !== undefined) {
                            this.has_scrolled_instant = true;
                            this.card_refs[card_idx].scrollIntoView({ behavior: "instant", block: "center" });
                        }

                        sessionStorage.removeItem('card_id');
                    });
                }

                if (card_id === null) {
                    this.will_scroll = true;
                    if (this.card_list_ref) {
                        this.$nextTick(() => {
                            if (this.card_refs[0] !== undefined) {
                                this.card_refs[0].scrollIntoView({ behavior: "instant", block: "center" });
                            }

                            this.$nextTick(() => {
                                this.card_list_ref.scrollIntoView({ behavior: "instant", block: "end" });
                                this.has_scrolled = true;
                            });
                        });
                    }
                }

                this.is_first_time = false;
            }
        },
        observeCardsPaging() {
            if (this.card_paging_observer === null) {
                this.card_paging_observer = new IntersectionObserver((entries) => {
                    for (let entry of entries) {
                        if (entry.isIntersecting) {
                            if (this.card_list_top_ref && this.card_list_top_ref === entry.target) {
                                const first = Math.min(...this.loaded_pages);
                                if (first > 1) {
                                    this.fetchCards(first - 1);
                                }
                            }

                            if (this.card_list_bottom_ref && this.card_list_bottom_ref === entry.target) {
                                const last = Math.max(...this.loaded_pages);
                                if (last < this.last_page) {
                                    this.fetchCards(last + 1);
                                }
                            }
                        }
                    }
                }, {
                    root: null,
                    rootMargin: '0px',
                    threshold: 0
                });

                if (this.card_scroll_section_ref) {
                    this.card_scroll_section_ref.addEventListener('scroll', () => {
                        setTimeout(() => {
                            if (this.card_list_top_ref) {
                                this.card_paging_observer.observe(this.card_list_top_ref);
                            }

                            if (this.card_list_bottom_ref) {
                                this.card_paging_observer.observe(this.card_list_bottom_ref);
                            }
                        }, 1000);
                    }, { once: true });
                }
            }
        },
        observeCardsFocus() {
            if (this.card_focus_observer) {
                this.card_focus_observer.disconnect();
                if (this.card_refs.length > 0) {
                    this.card_refs.forEach(el => this.card_focus_observer.observe(el));
                }
            }
        },
        setCardListTopRef(el) {
            if (el) {
                this.card_list_top_ref = el;
            }
        },
        setCardListBottomRef(el) {
            if (el) {
                this.card_list_bottom_ref = el;
            }
        },
        setCardListRef(el) {
            if (el) {
                this.card_list_ref = el;
            }
        },
        setCardScrollSectionRef(el) {
            if (el) {
                this.card_scroll_section_ref = el;
            }
        },
        setCardRefs(el) {
            if (el) {
                this.card_refs.push(el)
            }
        },
        clickCard(id) {
            const idx = this.cards.findIndex(card => card.id == id);

            if (idx > -1 && this.cards[idx] != undefined) {
                this.$nextTick(() => {
                    window.location.href = this.cards[idx].href;
                });
            }
        },
        fetchCards(page) {
            if (page === undefined) {
                page = 1;
            }

            if (this.is_loading || this.loaded_pages.includes(page)) {
                return;
            }

            this.is_loading = true;

            const first_loaded = Math.min(...this.loaded_pages);
            const is_prepend = page < first_loaded;

            let payload = {
                data: {}
            };

            if (this.paginate === true) {
                payload.data.paginate = true;
                payload.data.per_page = this.per_page;
                payload.data.page = page;
            }

            this.last_scroll_top = null;
            this.last_scroll_height = null;

            if (is_prepend && this.card_scroll_section_ref) {
                this.last_scroll_top = this.card_scroll_section_ref.scrollTop;
                this.last_scroll_height = this.card_scroll_section_ref.scrollHeight;
            }

            return _storage.post(_config.get("api.posts.index"), (_response) => {
                if (_storage.isSuccess(_response)) {
                    let response = _storage.getResponseData(_response);

                    if (_storage.hasPaginationData(response.posts)) {
                        let paginated_posts = _storage.getPaginationData(response.posts);

                        this.pages[paginated_posts.current_page] = paginated_posts.data;
                        this.loaded_pages.push(paginated_posts.current_page);

                        if (is_prepend) {
                            this.will_prepend = true;
                        }

                        this.is_loading = false;
                    } else {
                        this.pages[1] = response.posts;
                        this.loaded_pages.push(1);

                        this.is_loading = false;
                    }
                }
            }, payload);
        },
    },
    created() {
        this.pages[this.initial_page] = this.initial_cards;
        this.loaded_pages = [ this.initial_page ];
    },
    mounted() {
        if (this.card_focus_observer === null) {
            this.card_focus_observer = new IntersectionObserver((entries) => {
                if (this.cards_updating == false) {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            if (this.card_refs.length > 0) {
                                const idx = this.cards.findIndex(card => card.id == entry.target.id);
                                if (idx > -1 && this.cards[idx] != undefined) {
                                    this.focussed_id = this.cards[idx].id;
                                }
                            }
                        }
                    });
                }
            }, {
                root: null,
                rootMargin: '-45% 0px -45% 0px',
                threshold: 0
            });
        }

        window.addEventListener('pagereveal', this.onPageReveal);
        this.onLoad();
    },
    updated() {
        if (this.cards_updating) {
            if (this.will_prepend && this.card_scroll_section_ref) {
                const new_scroll_height = this.card_scroll_section_ref.scrollHeight;
                this.card_scroll_section_ref.scrollTop = this.last_scroll_top + (new_scroll_height - this.last_scroll_height);

                this.will_prepend = false;
                this.last_scroll_top = null;
                this.last_scroll_height = null;
            }

            this.$nextTick(() => {
                this.observeCardsFocus();
                this.cards_updating = false;
            });
        }
    },
    beforeUpdate() {
        this.card_refs = [];
    },
    beforeUnmount() {
        window.removeEventListener('pagereveal', this.onPageReveal);
        window.removeEventListener('load', this.onLoad);

        if (this.card_paging_observer) {
            this.card_paging_observer.disconnect();
        }

        if (this.card_focus_observer) {
            this.card_focus_observer.disconnect();
        }
    },
    template: `
    <slot
    :is_loading="is_loading"
    :card_focus_observer="card_focus_observer"
    :pre_cards="pre_cards"
    :cards="cards"
    :focussed_id="focussed_id"
    :clicked_id="clicked_id"
    :will_scroll="will_scroll"
    :has_scrolled="has_scrolled"
    :has_scrolled_instant="has_scrolled_instant"

    :setCardListTopRef="setCardListTopRef"
    :setCardListBottomRef="setCardListBottomRef"
    :setCardListRef="setCardListRef"
    :setCardScrollSectionRef="setCardScrollSectionRef"
    :setCardRefs="setCardRefs"
    :clickCard="clickCard"
    ></slot>
    `
};

export default _cms_cards;
