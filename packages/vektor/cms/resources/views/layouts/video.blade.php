@if (isset($section['attributes']['service']) && !empty($section['attributes']['service']) && isset($section['attributes']['code']) && !empty($section['attributes']['code']))
    @php
        $layout_classes = [
            "container:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
        ];
    @endphp
    <div section_type="{{ $section['layout'] }}" id="{{ $common_layout_id }}" class="{{ implode(" ", $common_layout_classes) }}"<?php if (!empty($styles_string)) {
        echo " style='{$styles_string}'";
    } ?>>
        @if ($cms_type == 'post' && $section_idx == 0)
            @include('cms::partials.post_breadcrumbs')
        @endif
        <div class="{{ implode(" ", $layout_classes) }}">
            @if ($cms_type == 'post' && $section_idx == 0)
                @include('cms::partials.post_header')
            @endif
            @if (in_array($section['attributes']['service'], ['vimeo', 'youtube']))
                @php
                    $iframe_classes = [
                        "video",
                        $section['attributes']['service'],
                        "aspect_ratio_" . (isset($section['attributes']['aspect_ratio']) && !empty($section['attributes']['aspect_ratio']) ? $section['attributes']['aspect_ratio'] : '16_9'),
                    ];
                @endphp
            @endif
            @if ($section['attributes']['service'] == 'vimeo')
                <iframe class="{{ implode(" ", $iframe_classes) }}" src="https://player.vimeo.com/video/{{ $section['attributes']['code'] }}?badge=0&autopause=0&player_id=0" frameborder="0" allow="autoplay; fullscreen; picture-in-picture; clipboard-write"></iframe>
            @endif
            @if ($section['attributes']['service'] == 'youtube')
                <iframe class="{{ implode(" ", $iframe_classes) }}" src="https://www.youtube.com/embed/{{ $section['attributes']['code'] }}?rel=0&modestbranding=1" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
            @endif
        </div>
    </div>
@endif