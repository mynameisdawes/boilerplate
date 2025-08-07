<?php

namespace Vektor\Events\Nova;

use App\Nova\Resource;
use DateTime as PHPDateTime;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\Date;
use Laravel\Nova\Fields\DateTime;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;

abstract class Event extends Resource
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
    public static $model = 'Vektor\Events\Models\Event';

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

    public function validate_time($time)
    {
        $format = 'H:i';
        $dateTime = PHPDateTime::createFromFormat($format, $time);

        return $dateTime && $dateTime->format($format) === $time;
    }

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
            Slug::make('Slug')->from('Title')->sortable(),
            Select::make('Status')->options([0 => 'Draft', 1 => 'Scheduled', 2 => 'Published'])->default(2)->displayUsingLabels()->rules('required'),
            DateTime::make('Publish Schedule Date', 'scheduled_at'),
            Text::make('Meta Title'),
            Text::make('Meta Description'),
            Image::make('Meta Image')->disk('public'),
            Date::make('Date')->rules('required'),
            Text::make('Time Start')->resolveUsing(function ($time) {
                return substr($time, 0, 5);
            })->rules('max:5', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->validate_time($value)) {
                    return $fail('The time start field must have a valid 24hr time format.');
                }
            }),
            Text::make('Time End')->resolveUsing(function ($time) {
                return substr($time, 0, 5);
            })->rules('max:5', function ($attribute, $value, $fail) {
                if (!empty($value) && !$this->validate_time($value)) {
                    return $fail('The time end field must have a valid 24hr time format.');
                }
            }),
            Boolean::make('Is Featured'),
            Text::make('Performance Title'),
            Text::make('Performance Href'),
            Textarea::make('Description')->alwaysShow(),
        ];
    }
}
