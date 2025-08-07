<header class="article_header">
    <div style="view-transition-name: _article_header_{{ $cms_entity->id }};">
        <h1 style="view-transition-name: card_item_header;">{{ $cms_entity->title }}</h1>
        <div class="collection metadata">
            @if ($cms_entity->author)
                <div>{{ $cms_entity->author }}</div>
            @endif
            @if ($cms_entity->formatted_publish_date)
                <div>{!! $cms_entity->formatted_publish_date !!}</div>
            @endif
        </div>
    </div>
    @if ($cms_entity->tags->count() > 0)
    <div class="mt-4">
        <div class="collection">
            @foreach ($cms_entity->tags as $tag)
                <div><a class="badge:xs" href="{{ str_replace('[cms_entity]', 'blog', $tag->href) }}">{{ $tag->name }}</a></div>
            @endforeach
        </div>
    </div>
    @endif
</header>