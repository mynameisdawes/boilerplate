<?php

namespace Vektor\ApiKeys\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;

abstract class ApiKey extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'User Management';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'Vektor\ApiKeys\Models\ApiKey';

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
    public static $title = 'name';

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
        'name',
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
            Text::make('Key')->rules('required')->hideWhenCreating()->hideWhenUpdating(),
            Text::make('Name')->rules('required'),
            Boolean::make('Is Active')->default(1),
        ];
    }
}
