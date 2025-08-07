@php
    $layout_classes = [
        "container:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
    ];

    if (isset($section['attributes']['content_inline_alignment']) && !empty($section['attributes']['content_inline_alignment'])) {
        $layout_classes[] = $section['attributes']['content_inline_alignment'];
    }

    $initial_markers = [];
    if (isset($section['attributes']['coordinates']) && !empty($section['attributes']['coordinates'])) {
        foreach ($section['attributes']['coordinates'] as $coordinate) {
            if (
                isset($coordinate['attributes'])
                && isset($coordinate['attributes']['latitude'])
                && !empty($coordinate['attributes']['latitude'])
                && isset($coordinate['attributes']['longitude'])
                && !empty($coordinate['attributes']['longitude'])
            ) {
                $initial_markers[] = [
                    'name' => isset($coordinate['attributes']['name']) && !empty($coordinate['attributes']['name']) ? $coordinate['attributes']['name'] : null,
                    'latitude' => $coordinate['attributes']['latitude'],
                    'longitude' => $coordinate['attributes']['longitude'],
                    'exclude_from_map_bounds' => $coordinate['attributes']['exclude_from_map_bounds'],
                ];
            }
        }
    }
@endphp
<div section_type="{{ $section['layout'] }}" id="{{ $common_layout_id }}" class="{{ implode(" ", $common_layout_classes) }}"<?php if (!empty($styles_string)) {
    echo " style='{$styles_string}'";
} ?>>
    <div class="{{ implode(" ", $layout_classes) }}">
        <c-map
        api_key="{{ config('map.api_key') }}"
        map_id="{{ config('map.id') }}"
        @if (!empty($initial_markers))
        :initial_markers='{!! json_encode($initial_markers) !!}'
        @endif
        place_autocomplete_url="api.locations.autocomplete.places"
        place_geocode_url="api.locations.geocode.place"
        @if (isset($section['attributes']['center_latitude']) && !empty($section['attributes']['center_latitude']))
            initial_latitude="{{ $section['attributes']['center_latitude'] }}"
        @endif
        @if (isset($section['attributes']['center_longitude']) && !empty($section['attributes']['center_longitude']))
            initial_longitude="{{ $section['attributes']['center_longitude'] }}"
        @endif
        >
            <template v-slot:default="mapScope">
                <div class="spinner__wrapper spinner--absolute" :class="{ is_loading: mapScope.is_loading == true }"><div class="spinner"></div></div>
                <div class="map" :class="{'map_mode': mapScope.mode == 'map', 'list_mode': mapScope.mode == 'list'}"></div>
            </template>
        </c-map>
    </div>
</div>