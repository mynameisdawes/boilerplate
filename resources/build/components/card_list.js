let _card_list = {
    name: "c-card_list",
    emits: [
    ],
    props: {
        initial_cards: {
            type: Array,
            default() {
                return [];
            }
        },
    },
    data() {
        return {
            is_loading: false,
            is_first_time: true,
            observer: null,
            card_list_ref: null,
            card_refs: [],
            pre_cards: [ {},{},{},{},{},{},{},{},{},{} ],
            cards: [],
            focussed_index: null,
            clicked_index: null,
            will_scroll: false,
            has_scrolled: false,
            has_scrolled_instant: false,
        };
    },
    watch: {
        cards: {
            handler(new_val, old_val) {
                this.$nextTick(this.observeCards);
            },
            deep: true
        }
    },
    computed: {
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

                if (card_id !== null) {
                    const card_idx = this.cards.findIndex(card => card.id === card_id);

                    if (card_idx !== -1) {
                        this.clicked_index = card_idx;
                        this.focussed_index = card_idx;
                    }

                    this.$nextTick(() => {
                        if (this.focussed_index !== null && this.card_refs[this.focussed_index] !== undefined) {
                            this.has_scrolled_instant = true;
                            this.card_refs[this.focussed_index].scrollIntoView({ behavior: "instant", block: "center", inline: "nearest" });
                        }

                        sessionStorage.removeItem('card_id');
                    });
                } else {
                    this.will_scroll = true;
                    if (this.card_list_ref) {
                        this.$nextTick(() => {
                            if (this.card_refs[0] !== undefined) {
                                this.card_refs[0].scrollIntoView({ behavior: "instant", block: "center", inline: "nearest" });
                            }
                            this.card_list_ref.scrollIntoView({ behavior: "instant", block: "end" });
                            this.has_scrolled = true;
                        });
                    }
                }

                this.is_first_time = false;
            }
        },
        observeCards() {
            if (this.observer) {
                this.observer.disconnect();
                if (this.card_refs.length > 0) {
                    this.card_refs.forEach(el => this.observer.observe(el));
                }
            }
        },
        setCardListRef(el) {
            if (el) {
                this.card_list_ref = el;
            }
        },
        setCardRefs(el) {
            if (el) {
                this.card_refs.push(el)
            }
        },
        clickCard(idx) {
            if (this.cards[idx] != undefined) {
                this.clicked_index = idx;
                this.$nextTick(() => {
                    window.location.href = this.cards[idx].url;
                });
            }
        },
        fetchCards() {
            this.is_loading = true;
            setTimeout(() => {
                this.cards = [
                    { url: "card_01", title: "Card 1", id: "card_01", img: "assets/img/background__01.webp" },
                    { url: "card_02", title: "Card 2", id: "card_02", img: "assets/img/background__02.webp" },
                    { url: "card_03", title: "Card 3", id: "card_03", img: "assets/img/background__01.webp" }
                ];

                this.onArrive();
                this.is_loading = false;
            }, 0);
        }
    },
    created() {
        if (this.initial_cards.length > 0) {
            this.cards = [...this.initial_cards];
        } else {
            this.fetchCards();
        }
    },
    mounted() {
        this.observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    if (this.card_refs.length > 0) {
                        const idx = this.card_refs.findIndex(el => el === entry.target);
                        this.focussed_index = idx;
                    }
                }
            });
        }, {
            root: null,
            rootMargin: '-45% 0px -45% 0px',
            threshold: 0
        });

        window.addEventListener('pagereveal', this.onPageReveal);
        this.onLoad();

        this.$nextTick(this.observeCards);
    },
    beforeUpdate() {
        this.card_list_ref = null;
        this.card_refs = [];
    },
    beforeUnmount() {
        window.removeEventListener('pagereveal', this.onPageReveal);
        window.removeEventListener('load', this.onLoad);

        if (this.observer) {
            this.observer.disconnect();
        }
    },
    template: `
    <slot
    :is_loading="is_loading"
    :observer="observer"
    :pre_cards="pre_cards"
    :cards="cards"
    :focussed_index="focussed_index"
    :clicked_index="clicked_index"
    :will_scroll="will_scroll"
    :has_scrolled="has_scrolled"
    :has_scrolled_instant="has_scrolled_instant"

    :setCardListRef="setCardListRef"
    :setCardRefs="setCardRefs"
    :clickCard="clickCard"
    ></slot>
    `
};

export default _card_list;
