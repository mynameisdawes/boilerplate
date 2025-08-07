<?php

namespace Vektor\Pages\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Vektor\CMS\Services\CMSService;
use Whitecube\NovaFlexibleContent\Flexible;

abstract class Page extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'CMS';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'Vektor\Pages\Models\Page';

    /**
     * Hide resource from Nova's standard menu.
     *
     * @var bool
     */
    public static $displayInNavigation = true;

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'title';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'title',
        'slug',
    ];

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     *
     * @throws \Exception
     */
    public function fields(Request $request)
    {
        $layout_fields = CMSService::layout_fields($request);

        return [
            ID::make()->sortable(),
            Text::make('Title')->rules('required'),
            Slug::make('Slug')->from('Title')->sortable(),
            Select::make('Status')->options([0 => 'Draft', 1 => 'Scheduled', 2 => 'Published'])->default(2)->displayUsingLabels()->rules('required'),
            DateTime::make('Publish Schedule Date', 'scheduled_at'),
            Text::make('Meta Title'),
            Text::make('Meta Description'),
            Image::make('Meta Image')->disk('public'),
            Flexible::make('Content')->fullWidth()->confirmRemove()
                ->addLayout('Card Section', 'card', $layout_fields['card'])->collapsed(true)
                ->addLayout('CTA Section', 'cta', $layout_fields['cta'])->collapsed(true)
                ->addLayout('Gallery Section', 'gallery', $layout_fields['gallery'])->collapsed(true)
                ->addLayout('Hero Section', 'hero', $layout_fields['hero'])->collapsed(true)
                ->addLayout('Image/Markdown Section', 'image_markdown', $layout_fields['image_markdown'])->collapsed(true)
                ->addLayout('Map Section', 'map', $layout_fields['map'])->collapsed(true)
                ->addLayout('Products Section', 'products', $layout_fields['products'])->collapsed(true)
                ->addLayout('Promo Section', 'promo', $layout_fields['promo'])->collapsed(true)
                ->addLayout('Template Section', 'template', $layout_fields['template'])->collapsed(true)
                ->addLayout('USP Section', 'usps', $layout_fields['usps'])->collapsed(true)
                ->addLayout('Video Section', 'video', $layout_fields['video'])->collapsed(true),
            KeyValue::make('Metadata')->rules('json'),
            Text::make('Preview')
                ->withMeta([
                    'value' => $this->slug ? '<a href="'.route('pages.show', ['slug' => $this->slug]).'" target="_blank">Preview</a>' : null,
                ])
                ->asHtml()
                ->onlyOnIndex(),
        ];
    }
}
