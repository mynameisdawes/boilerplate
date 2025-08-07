<?php

namespace Vektor\Blog\Nova;

use App\Nova\Resource;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\BelongsTo;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Image;
use Laravel\Nova\Fields\Slug;
use Laravel\Nova\Fields\Text;

abstract class PostCategory extends Resource
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
    public static $model = 'Vektor\Blog\Models\PostCategory';

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
            Text::make('Name'),
            Slug::make('Slug')->from('Name')->sortable(),

            Text::make('Meta Title'),
            Text::make('Meta Description'),
            Image::make('Meta Image')->disk('public'),

            BelongsTo::make('Parent', 'parent', 'App\Nova\PostCategory')->nullable(),
            HasMany::make('Children', 'children', 'App\Nova\PostCategory'),

            BelongsToMany::make('Posts'),
        ];
    }
}
