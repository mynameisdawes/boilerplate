@php
    $content_position = isset($section['attributes']['content_position']) && !empty($section['attributes']['content_position']) ? $section['attributes']['content_position'] : 'placed_on_background';

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
        @if (isset($section['attributes']['markdown']))
            <div class="card_content">
                <?php $button_section = $section; ?>
                <div class="article">
                    @markdown($section['attributes']['markdown'])
                </div>
                @include('cms::partials.buttons')
            </div>
        @endif
    </div>
</div>