@if (isset($section['attributes']['gallery']) && !empty($section['attributes']['gallery']))
    @php
        $layout_classes = [
            "swiper-gallery",
            "swiper:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
        ];

        if (isset($section['attributes']['aspect_ratio']) && !empty($section['attributes']['aspect_ratio'])) {
            $layout_classes[] = "aspect_ratio_{$section['attributes']['aspect_ratio']}";
        }
    @endphp
    <div section_type="{{ $section['layout'] }}" id="{{ $common_layout_id }}" class="{{ implode(" ", $common_layout_classes) }}"<?php if (!empty($styles_string)) {
        echo " style='{$styles_string}'";
    } ?>>
        @if ($cms_type == 'post' && $section_idx == 0)
            @include('cms::partials.post_breadcrumbs')
            @php
                $post_layout_classes = [
                    "container:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
                ];
            @endphp
            <div class="{{ implode(" ", $post_layout_classes) }}">
                @include('cms::partials.post_header')
            </div>
        @endif
        <div class="swiper_outer_wrapper">
            <swiper
            class="{{ implode(" ", $layout_classes) }}"
            :modules="swiper_modules"
            :pagination="swiper_pagination_options"
            :navigation="swiper_navigation_options"
            :breakpoints="swiper_breakpoints"
            :centered-slides="<?php echo (isset($section['attributes']['centered_slides']) && true == $section['attributes']['centered_slides']) ? 'true' : 'false'; ?>"
            >
                @foreach ($section['attributes']['gallery'] as $gallery_slide)
                    <swiper-slide>
                        <img src="{{ url('storage/' . $gallery_slide['attributes']['image']) }}" alt="{{ $gallery_slide['attributes']['image_alt'] }}" />
                    </swiper-slide>
                @endforeach
                @include('cms::partials.swiper_arrows')
            </swiper>
        </div>
    </div>
@endif