@if (isset($section['attributes']['image/markdown']) && !empty($section['attributes']['image/markdown']))
    <div section_type="{{ $section['layout'] }}" id="{{ $common_layout_id }}" class="{{ implode(" ", $common_layout_classes) }}"<?php if (!empty($styles_string)) {
        echo " style='{$styles_string}'";
    } ?>>
        <div class="container-gutter:inner">
            @foreach ($section['attributes']['image/markdown'] as $section_image_markdown_idx => $section_image_markdown)
                @php
                    $section_layout_id = "section_{$section_image_markdown['key']}";
                    if (isset($section_image_markdown['attributes']['container_label']) && !empty($section_image_markdown['attributes']['container_label'])) {
                        $section_layout_id = Illuminate\Support\Str::snake($section_image_markdown['attributes']['container_label']);
                    }

                    $content_position = isset($section_image_markdown['attributes']['content_position']) && !empty($section_image_markdown['attributes']['content_position']) ? $section_image_markdown['attributes']['content_position'] : 'image_top_markdown_bottom';

                    if ($content_position == 'image_top_markdown_bottom') {
                        $layout_classes = [
                            "image_top_markdown_bottom",
                            "container:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
                            isset($section_image_markdown['attributes']['image_behaviour']) && !empty($section_image_markdown['attributes']['image_behaviour']) ? $section_image_markdown['attributes']['image_behaviour'] : 'to_container_edge',
                        ];
                    } else {
                        $layout_classes = [
                            "{$content_position}:" . (isset($section['attributes']['container_width']) && !empty($section['attributes']['container_width']) ? $section['attributes']['container_width'] : 'xl'),
                            isset($section_image_markdown['attributes']['content_block_alignment']) && !empty($section_image_markdown['attributes']['content_block_alignment']) ? $section_image_markdown['attributes']['content_block_alignment'] : 'to_center',
                            isset($section_image_markdown['attributes']['image_behaviour']) && !empty($section_image_markdown['attributes']['image_behaviour']) ? $section_image_markdown['attributes']['image_behaviour'] : 'to_container_edge',
                        ];
                    }

                    if (isset($section_image_markdown['attributes']['content_inline_alignment']) && !empty($section_image_markdown['attributes']['content_inline_alignment'])) {
                        $layout_classes[] = $section_image_markdown['attributes']['content_inline_alignment'];
                    }
                @endphp
                @if ($cms_type == 'post' && $section_idx == 0 && $section_image_markdown_idx == 0)
                    @include('cms::partials.post_breadcrumbs')
                @endif
                <div id="{{ $section_layout_id }}" class="{{ implode(" ", $layout_classes) }}">
                    @if ($cms_type == 'post' && $section_idx == 0 && $section_image_markdown_idx == 0)
                        @include('cms::partials.post_header')
                    @endif
                    @if (isset($section_image_markdown['attributes']['image']))
                        <div class="visual">
                            <img src="{{ url('storage/' . $section_image_markdown['attributes']['image']) }}" alt="{{ $section_image_markdown['attributes']['image_alt'] }}" />
                        </div>
                    @endif
                    @if (isset($section_image_markdown['attributes']['markdown']))
                        <?php $button_section = $section_image_markdown; ?>
                        <div class="textual">
                            <div class="article">
                                @markdown($section_image_markdown['attributes']['markdown'])
                            </div>
                            @include('cms::partials.buttons')
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif