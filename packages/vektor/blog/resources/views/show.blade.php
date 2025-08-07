@extends('layouts.default')
@if (isset($cms_entity))
    @section('title', $cms_entity->title)
    @section('meta_title', !empty($cms_entity->meta_title) ? $cms_entity->meta_title : $cms_entity->title)
    @section('meta_description', $cms_entity->formatted_meta_description)
    @section('meta_image', $cms_entity->formatted_meta_image)
    @section('canonical_url', $cms_entity->href)
@endif

@section('content')
    @if (!@empty($cms_entity->content))
        @foreach($cms_entity->content as $section_idx => $section)
            @php
                $common_layout_id = "section_{$section['key']}";
                if (isset($section['attributes']['container_label']) && !empty($section['attributes']['container_label'])) {
                    $common_layout_id = Illuminate\Support\Str::snake($section['attributes']['container_label']);
                }

                $common_layout_classes = [
                    "container-gutter:outer",
                ];

                if (
                    (
                        !isset($section['attributes']['background_image_-_landscape_viewport'])
                        && !isset($section['attributes']['background_image_-_portrait_viewport'])
                        && !isset($section['attributes']['background_colour'])
                    ) || in_array($section['layout'], [
                        'card',
                        'promo',
                    ])
                ) {
                    $common_layout_classes[] = "no-bg";
                }

                if (isset($section['attributes']['background_colour']) && !empty($section['attributes']['background_colour'])) {
                    if (!in_array($section['layout'], [
                        'promo',
                    ])) {
                        $common_layout_classes[] = "bg-{$section['attributes']['background_colour']}";
                    }
                }

                if (isset($section['attributes']['content_mode']) && !empty($section['attributes']['content_mode'])) {
                    if ($section['attributes']['content_mode'] == 'dark') {
                        $common_layout_classes[] = 'text-white';
                    }
                }

                $styles_array = [];
                if ((isset($section['attributes']['background_image_-_landscape_viewport']) && !empty($section['attributes']['background_image_-_landscape_viewport'])) || (isset($section['attributes']['background_image_-_portrait_viewport']) && !empty($section['attributes']['background_image_-_portrait_viewport']))) {
                    if ((isset($section['attributes']['background_image_-_landscape_viewport']) && !empty($section['attributes']['background_image_-_landscape_viewport']))) {
                        $styles_array['--_landscape_background_image'] = "url(" . url('storage/' . $section['attributes']['background_image_-_landscape_viewport']) . ")";
                    }
                    if ((isset($section['attributes']['background_image_-_portrait_viewport']) && !empty($section['attributes']['background_image_-_portrait_viewport']))) {
                        $styles_array['--_portrait_background_image'] = "url(" . url('storage/' . $section['attributes']['background_image_-_portrait_viewport']) . ")";
                    }
                }

                if (isset($section['attributes']['background_colour']) && !empty($section['attributes']['background_colour'])) {
                    if (stripos($section['attributes']['background_colour'], 'gradient') !== false) {
                        $styles_array['--_background_image'] = "var(--color_" . $section['attributes']['background_colour'] . ")";
                    } else {
                        $styles_array['--_background_color'] = "var(--color_" . $section['attributes']['background_colour'] . ")";
                    }
                }
                $styles_string = Vektor\Utilities\Utilities::arrayToInlineStyles($styles_array);
            @endphp

            @if (in_array($section['layout'], [
                'card',
                'cta',
                'gallery',
                'hero',
                'image_markdown',
                'map',
                'products',
                'promo',
                'template',
                'usps',
                'video',
            ]))
                @include("cms::layouts.{$section['layout']}", ['cms_entity' => $cms_entity, 'cms_type' => $cms_type, 'section_idx' => $section_idx])
            @endif
        @endforeach

        @php
            $cms_entity_related_entities = $cms_entity->relatedByCategories();
        @endphp
        @if ($cms_entity_related_entities->count() > 0)
            <div class="container-gutter:outer no-bg">
                <div class="container:xl">
                    <header class="h3 mb-0">Related Posts</header>
                    <ul>
                        @foreach ($cms_entity_related_entities as $cms_entity_related_entity)
                            <li><a href="{{ $cms_entity_related_entity->href }}">{{ $cms_entity_related_entity->title }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif
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
                            { "href_matches": "/cards" }
                        ]
                    }}
                ]
            },
            "eagerness": "moderate"
        },
        {
            "source": "document",
            "where": {
                "href_matches": "/cards"
            },
            "eagerness": "eager"
        }
    ]
}
</script>
@endsection

@section('extra')
@if (isset($cms_entity))
    <?php request()->session()->put('post_id', $cms_entity->id); ?>
    <script>
        window.addEventListener('load', () => {
            sessionStorage.setItem('post_id', '{{ $cms_entity->id }}');
        });
        window.addEventListener('pageswap', () => {
            sessionStorage.setItem('post_id', '{{ $cms_entity->id }}');
        });
    </script>
@endif
@endsection