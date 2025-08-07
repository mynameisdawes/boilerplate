<?php

namespace Vektor\CMS\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Text;

abstract class Navigation extends Resource
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
    public static $model = 'Vektor\CMS\Models\Navigation';

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
            Text::make('Title')->rules('required'),
            Boolean::make('Is Enabled')->default(1),
            Number::make('Sort Order')->default(0),
            HasMany::make('Items', 'items', 'App\Nova\NavigationItem'),
        ];
    }
}
