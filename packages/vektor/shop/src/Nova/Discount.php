<?php

namespace Vektor\Shop\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;

abstract class Discount extends Resource
{
    /**
     * The logical group associated with the resource.
     *
     * @var string
     */
    public static $group = 'Ecommerce';

    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = 'Vektor\Shop\Models\Discount';

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
        'amount',
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
            Text::make('Name')->rules('required'),
            Text::make('Blurb'),
            Number::make('Amount')->rules('required'),
            Select::make('Type')->options([
                'percentage' => 'Percentage',
                // 'fixed' => 'Fixed',
            ])->default('percentage')->displayUsingLabels()->rules('required'),
            Text::make('CTA URL'),
            Text::make('CTA Text'),
            Date::make('Start Date'),
            Date::make('End Date'),

            HasMany::make('Discount Codes', 'discount_codes'),
        ];
    }
}
