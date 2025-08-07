@if (isset($section['attributes']['usps']) && !empty($section['attributes']['usps']))
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
            <?php
            $usp_max_columns = 2;
    $usp_count = count($section['attributes']['usps']);
    for ($i = 3; $i >= 2; --$i) {
        if (0 === $usp_count % $i) {
            $usp_max_columns = $i;

            break;
        }
    }
    ?>
            <div class="usps" style="--usp_max_columns: {{ $usp_max_columns }}">
                @foreach ($section['attributes']['usps'] as $usp)
                    <div class="usp">
                        @if (isset($usp['attributes']['image']))
                            <div class="visual">
                                <img src="{{ url('storage/' . $usp['attributes']['image']) }}" alt="{{ $usp['attributes']['image_alt'] }}" />
                            </div>
                        @endif
                        @if (isset($usp['attributes']['markdown']))
                            <?php $button_section = $usp; ?>
                            <div class="textual">
                                <div class="article">
                                    @markdown($usp['attributes']['markdown'])
                                </div>
                                @include('cms::partials.buttons')
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif