<div class="document__header__actions">
    <div class="document__navigation_strip">
        <div class="container:xl">
            <div class="content__wrapper">
                <div class="content">
                    <ul class="breadcrumbs">
                        <li><a href="{{ url('blog') }}">Back to Blog</a></li>
                        @if (isset($cms_entity) && isset($cms_type) && $cms_type == 'post' && $cms_entity->categories->count() > 0)
                            @php
                                $cms_entity_category = $cms_entity->categories()->orderBy('created_at', 'desc')->first();
                                $cms_entity_category_ancestors = $cms_entity_category->ancestors();
                            @endphp
                            @if ($cms_entity_category_ancestors->count() > 0)
                                @foreach ($cms_entity_category_ancestors as $cms_entity_category_ancestor)
                                    <li><a href="{{ $cms_entity_category_ancestor->href }}">{{ $cms_entity_category_ancestor->name }}</a></li>
                                @endforeach
                            @endif
                            <li><a href="{{ str_replace('[cms_entity]', 'blog', $cms_entity_category->href) }}">{{ $cms_entity_category->name }}</a></li>
                        @endif
                        <li>{{ $cms_entity->title }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>