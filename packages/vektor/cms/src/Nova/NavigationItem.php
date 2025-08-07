<?php

namespace Vektor\CMS\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\KeyValue;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

abstract class NavigationItem extends Resource
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
    public static $model = 'Vektor\CMS\Models\NavigationItem';

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
        return [
            ID::make()->sortable(),
            BelongsTo::make('Navigation', 'navigation', 'App\Nova\Navigation')->nullable(),
            BelongsTo::make('Parent', 'parent', 'App\Nova\NavigationItem')->nullable(),
            Text::make('Title')->rules('required'),
            Select::make('Linked Model Name')->options([
                'Vektor\CMS\Models\Page' => 'Page',
            ])->displayUsingLabels()->nullable(),
            Text::make('Linked Model Id'),
            Text::make('Slug'),
            KeyValue::make('Attributes')->rules('json'),
            Boolean::make('Is Enabled')->default(1),
            Number::make('Sort Order')->default(0),
        ];
    }
}
