<footer class="document__footer document__footer--fixed" role="contentinfo" aria-label="Main Footer">
    <section class="bg-primary text-primary_contrasting">
        <div class="container:xl">
            <div class="footer__content__wrapper">
                <div class="footer__content">{{ config('app.company.name') }} &copy; @{{ year }}</div>
                <div class="footer__content">
                    <div class="collection metadata">
                        <?php
                        $navigation_items = [];

                        if (class_exists('Vektor\CMS\Services\NavigationService')) {
                            $navigation_service = new Vektor\CMS\Services\NavigationService();
                            $subfooter_navigation = $navigation_service->fetch('subfooter');
                            if ($subfooter_navigation && $subfooter_navigation->items->count() > 0) {
                                $navigation_items = $subfooter_navigation->items;
                            }
                        }
                        ?>
                        @if (!empty($navigation_items))
                            @foreach ($navigation_items as $navigation_item)
                                <div><a href="{{ $navigation_item->href }}">{{ $navigation_item->title }}</a></div>
                            @endforeach
                        @endif
                        <div>website by <a href="https://vektor.co.uk" target="_blank" rel="noopener">vektor</a></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</footer>
@include('partials.cookies')
@if (config('app.search.enabled') == true && config('shop.only') === false)
    <c-search :trigger="search_trigger" @open="search_trigger = true" @close="search_trigger = false">
        <template v-slot:default="search">
            <c-input @select="search.select" name="s" v-model="search.s" :suggestions="countries" :suggestions_model="false" type="autocomplete" placeholder="Enter search terms"></c-input><input @click.prevent="search.search" class="btn" type="submit" value="" />
        </template>
    </c-search>
@endif