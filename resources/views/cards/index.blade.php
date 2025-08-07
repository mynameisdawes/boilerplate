@extends('layouts.blank')
@section('title', 'Cards')

@section('content')
@if (!empty($initial_posts))
    <c-cms_cards :initial_cards='@json($initial_posts)' :initial_page="{{ $initial_page }}" :last_page="{{ $last_page }}" :per_page="{{ $per_page }}" :paginate="{{ $paginate ? 'true' : 'false' }}">
        <template v-slot:default="cardScope">
            <div class="spinner__wrapper" :class="{ is_loading: cardScope.is_loading == true }"><div class="spinner"></div></div>
            <div class="card-list-outer" :class="{ will_scroll: cardScope.will_scroll, has_scrolled: cardScope.has_scrolled, has_scrolled_instant: cardScope.has_scrolled_instant }" :ref="cardScope.setCardListRef">
                <div class="card-list-inner">
                    <section class="pre-scroll-section" v-if="cardScope.pre_cards.length > 0">
                        <a class="card-item" v-for="pre_card in cardScope.pre_cards"></a>
                    </section>
                    <section class="scroll-section" :class="{ is_observed: cardScope.card_focus_observer !== null }" :ref="cardScope.setCardScrollSectionRef">
                        <div :ref="cardScope.setCardListTopRef"></div>
                        <a class="card-item" :href="card.href" :ref="cardScope.setCardRefs" :id="card.id" :key="card.id" v-for="card in cardScope.cards" :class="{ is_focussed: cardScope.focussed_id === card.id }" @click.prevent="cardScope.clickCard(card.id)">
                            <img :src="card.formatted_meta_image" :style="{ viewTransitionName: cardScope.clicked_id == card.id ? 'card_item_img' : null }" />
                            {{-- <img :src="'assets/img/background__01.webp'" :style="{ viewTransitionName: cardScope.clicked_id == card.id ? 'card_item_img' : null }" /> --}}
                            <header>
                                <div :style="{ viewTransitionName: cardScope.clicked_id == card.id ? 'card_item_header' : null }">@{{ card.title }}</div>
                            </header>
                        </a>
                        <div :ref="cardScope.setCardListBottomRef"></div>
                    </section>
                </div>
            </div>
        </template>
    </c-cms_cards>
@endif
@endsection

@section('speculationrules')
<script type="speculationrules">
{
    "prerender": [
        {
            "source": "document",
            "where": {
                "and": [
                    { "href_matches": "/*" },
                    { "not": {
                        "or": [
                            { "href_matches": "/logout/*" },
                            { "href_matches": "/card_0*" }
                        ]
                    }}
                ]
            },
            "eagerness": "moderate"
        },
        {
            "source": "document",
            "where": {
                "href_matches": "/card_0*"
            },
            "eagerness": "eager"
        }
    ]
}
</script>
@endsection