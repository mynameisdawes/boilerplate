@if (isset($button_section['attributes']['buttons']) && !empty($button_section['attributes']['buttons']))
    <div class="collection">
        @foreach ($button_section['attributes']['buttons'] as $button)
            @php
                $button_classes = [
                    "btn"
                ];

                if (isset($button['attributes']['background_colour']) && !empty($button['attributes']['background_colour'])) {
                    $button_classes[] = "bg-{$button['attributes']['background_colour']}";
                    $border_color = str_replace(['_linear_gradient', '_radial_gradient'], '', $button['attributes']['background_colour']);
                    $button_classes[] = "border-{$border_color}";
                }

                if (isset($button['attributes']['text_colour']) && !empty($button['attributes']['text_colour'])) {
                    $button_classes[] = "text-{$button['attributes']['text_colour']}";
                }
            @endphp
            @if ($button['layout'] == 'button' && !empty($button['attributes']['text']) && !empty($button['attributes']['url']))
                @php
                    $button_url = $button['attributes']['url'];
                    $parsed_url = parse_url($button['attributes']['url']);
                    if (!isset($parsed_url["scheme"])) {
                        $button_url = url($button_url);
                    }

                    $open_new_tab = (isset($button['attributes']['open_new_tab']) && $button['attributes']['open_new_tab'] === true) ? true : false;
                @endphp
                <a class="{{ implode(" ", $button_classes) }}" href="{{ $button_url }}"<?php if ($open_new_tab) {
                    echo " target='_blank'";
                } ?>>{{ $button['attributes']['text'] }}</a>
            @endif
        @endforeach
    </div>
@endif