@php
    $layout_classes = [
        "container:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
    ];

    if (isset($section['attributes']['content_inline_alignment']) && !empty($section['attributes']['content_inline_alignment'])) {
        $layout_classes[] = $section['attributes']['content_inline_alignment'];
    }
@endphp
<div section_type="{{ $section['layout'] }}" id="{{ $common_layout_id }}" class="{{ implode(" ", $common_layout_classes) }}"<?php if (!empty($styles_string)) {
    echo " style='{$styles_string}'";
} ?>>
    <div class="{{ implode(" ", $layout_classes) }}">
        @php
        $ids = null;
        if (!empty($section['attributes']['product_ids'])) {
            $ids = array_map('trim', explode(',', $section['attributes']['product_ids']));
        }

        if ($section['attributes']['filters'] == null) {
            $section['attributes']['filters'] = false;
        }
        @endphp

        @include('shop::partials.index', ['ids' => $ids, 'filters' => $section['attributes']['filters'], 'paginate' => $section['attributes']['paginate'], 'per_pages' => $section['attributes']['per_pages']])
    </div>
</div>