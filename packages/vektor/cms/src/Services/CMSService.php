<?php

namespace Vektor\CMS\Services;

use Datomatic\NovaMarkdownTui\MarkdownTui;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Whitecube\NovaFlexibleContent\Flexible;

class CMSService
{
    public static function layout_fields(Request $request)
    {
        $aspect_ratio_options = [
            '16_9' => '16 / 9',
            '4_3' => '4 / 3',
            '9_16' => '9 / 16',
            '3_4' => '3 / 4',
            '1' => '1 / 1',
        ];

        $container_size_options = [
            'xs' => 'Extra Small',
            'sm' => 'Small',
            'md' => 'Medium',
            'lg' => 'Large',
            'xl' => 'Extra Large',
        ];

        $bg_colors = [
            'background' => 'Background',
            'background_contrasting' => 'Background Contrasting',
            'primary' => 'Primary',
            'primary_linear_gradient' => 'Primary Linear Gradient',
            'primary_radial_gradient' => 'Primary Radial Gradient',
            'secondary' => 'Secondary',
            'secondary_linear_gradient' => 'Secondary Linear Gradient',
            'secondary_radial_gradient' => 'Secondary Radial Gradient',
        ];

        $text_colors = [
            'background' => 'Background',
            'background_contrasting' => 'Background Contrasting',
            'primary' => 'Primary',
            'primary_contrasting' => 'Primary Contrasting',
            'secondary' => 'Secondary',
            'secondary_contrasting' => 'Secondary Contrasting',
        ];

        $button_common_fields = [
            Boolean::make('Open New Tab'),
            Select::make('Background Colour')->options($bg_colors)->displayUsingLabels()->nullable(),
            Select::make('Text Colour')->options($text_colors)->displayUsingLabels()->nullable(),
            Text::make('Text'),
            Text::make('URL'),
        ];

        $coordinate_common_fields = [
            Text::make('Name'),
            Text::make('Latitude'),
            Text::make('Longitude'),
            Boolean::make('Exclude From Map Bounds'),
        ];

        $container_common_fields = [
            Text::make('Container Label')->nullable(),
            Select::make('Container Width')->options($container_size_options)->displayUsingLabels(),
            Select::make('Content Mode')->options([
                'light' => 'Light mode',
                'dark' => 'Dark mode',
            ])->displayUsingLabels()->nullable(),
            Select::make('Background Colour')->options($bg_colors)->displayUsingLabels()->nullable(),
            Image::make('Background Image - Landscape Viewport')->disk('public')->prunable(),
            Image::make('Background Image - Portrait Viewport')->disk('public')->prunable(),
        ];

        $card_fields = [
            Select::make('Content Inline Alignment')->options([
                'to_inline_start' => 'Align to left',
                'to_inline_center' => 'Align to center',
                'to_inline_end' => 'Align to right',
            ])->displayUsingLabels()->nullable(),
            MarkdownTui::make('Markdown')->fullWidth(),
            Flexible::make('Buttons')->fullWidth()->addLayout('Button', 'button', $button_common_fields)->button('Add button')->confirmRemove(),
        ];

        $card_fields = array_merge($container_common_fields, $card_fields);

        $cta_fields = [
            Select::make('Content Inline Alignment')->options([
                'to_inline_start' => 'Align to left',
                'to_inline_center' => 'Align to center',
                'to_inline_end' => 'Align to right',
            ])->displayUsingLabels()->nullable(),
            Select::make('Content Position')->options([
                'placed_on_background' => 'Placed on background',
                'placed_on_box' => 'Placed on box',
            ])->displayUsingLabels(),
            MarkdownTui::make('Markdown')->fullWidth(),
            Flexible::make('Buttons')->fullWidth()->addLayout('Button', 'button', $button_common_fields)->button('Add button')->confirmRemove(),
        ];

        $cta_fields = array_merge($container_common_fields, $cta_fields);

        $gallery_common_fields = [
            Image::make('Image')->disk('public')->prunable(),
            Text::make('Image Alt'),
        ];

        $gallery_fields = [
            Select::make('Aspect Ratio')->options($aspect_ratio_options)->default('4 / 3')->displayUsingLabels()->nullable(),
            Boolean::make('Centered Slides'),
            Flexible::make('Gallery')->fullWidth()->addLayout('Gallery', 'gallery', $gallery_common_fields)->button('Add Gallery Image')->confirmRemove(),
        ];

        $gallery_fields = array_merge($container_common_fields, $gallery_fields);

        $hero_fields = [
            Select::make('Content Inline Alignment')->options([
                'to_inline_start' => 'Align to left',
                'to_inline_center' => 'Align to center',
                'to_inline_end' => 'Align to right',
            ])->displayUsingLabels()->nullable(),
            MarkdownTui::make('Markdown')->fullWidth(),
            Flexible::make('Buttons')->fullWidth()->addLayout('Button', 'button', $button_common_fields)->button('Add button')->confirmRemove(),
        ];

        $hero_fields = array_merge($container_common_fields, $hero_fields);

        $image_markdown_fields = [
            Flexible::make('Image/Markdown')->fullWidth()->addLayout('Image/Markdown', 'image_markdown', [
                Text::make('Container Label')->nullable(),
                Select::make('Content Inline Alignment')->options([
                    'to_inline_start' => 'Align to left',
                    'to_inline_center' => 'Align to center',
                    'to_inline_end' => 'Align to right',
                ])->displayUsingLabels()->nullable(),
                Select::make('Content Block Alignment')->options([
                    'to_block_start' => 'Align to top',
                    'to_block_center' => 'Align to center',
                    'to_block_end' => 'Align to bottom',
                ])->displayUsingLabels()->nullable(),
                Select::make('Content Position')->options([
                    'image_top_markdown_bottom' => 'Image top, Markdown bottom',
                    'image_left_markdown_right' => 'Image left, Markdown right',
                    'image_right_markdown_left' => 'Image right, Markdown left',
                ])->displayUsingLabels()->nullable(),
                Select::make('Image Behaviour')->options([
                    'to_container_edge' => 'Align to container edge',
                    'to_viewport_edge' => 'Align to viewport edge',
                ])->displayUsingLabels()->nullable(),
                Image::make('Image')->disk('public')->prunable(),
                Text::make('Image Alt'),
                MarkdownTui::make('Markdown')->fullWidth(),
                Flexible::make('Buttons')->fullWidth()->addLayout('Button', 'button', $button_common_fields)->button('Add button')->confirmRemove(),
            ])->button('Add image/markdown content')->confirmRemove(),
        ];

        $image_markdown_fields = array_merge($container_common_fields, $image_markdown_fields);

        $map_fields = [
            Text::make('Center Latitude'),
            Text::make('Center Longitude'),
            Flexible::make('Coordinates')->fullWidth()->addLayout('Coordinate', 'coordinate', $coordinate_common_fields)->button('Add coordinate')->confirmRemove(),
        ];

        $map_fields = array_merge($container_common_fields, $map_fields);

        $promo_fields = [
            Text::make('Discount ID'),
        ];

        $promo_fields = array_merge($container_common_fields, $promo_fields);

        $products_fields = [
            Text::make('Product IDs'),
            Boolean::make('Filters'),
            Boolean::make('Paginate'),
            Number::make('Per Pages'),
        ];

        $products_fields = array_merge($container_common_fields, $products_fields);

        $template_fields = [
            KeyValue::make('Data')->rules('json'),
            Text::make('Template'),
        ];

        $usp_common_fields = [
            Image::make('Image')->disk('public')->prunable(),
            Text::make('Image Alt'),
            MarkdownTui::make('Markdown')->fullWidth(),
            Flexible::make('Buttons')->fullWidth()->addLayout('Button', 'button', $button_common_fields)->button('Add button')->confirmRemove(),
        ];

        $usps_fields = [
            Select::make('Content Inline Alignment')->options([
                'to_inline_start' => 'Align to left',
                'to_inline_center' => 'Align to center',
                'to_inline_end' => 'Align to right',
            ])->displayUsingLabels()->nullable(),
            Flexible::make('USPs')->fullWidth()->addLayout('USP', 'usp', $usp_common_fields)->button('Add USP')->confirmRemove(),
        ];

        $usps_fields = array_merge($container_common_fields, $usps_fields);

        $video_fields = [
            Select::make('Service')->options([
                'vimeo' => 'Vimeo',
                'youtube' => 'YouTube',
            ])->displayUsingLabels(),
            Text::make('Code'),
            Select::make('Aspect Ratio')->options($aspect_ratio_options)->default('16 / 9')->displayUsingLabels()->nullable(),
        ];

        $video_fields = array_merge($container_common_fields, $video_fields);

        return [
            'card' => $card_fields,
            'cta' => $cta_fields,
            'gallery' => $gallery_fields,
            'hero' => $hero_fields,
            'image_markdown' => $image_markdown_fields,
            'map' => $map_fields,
            'products' => $products_fields,
            'promo' => $promo_fields,
            'template' => $template_fields,
            'usps' => $usps_fields,
            'video' => $video_fields,
        ];
    }
}
