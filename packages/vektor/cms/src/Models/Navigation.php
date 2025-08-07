<?php

namespace Vektor\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Navigation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'is_enabled',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * The attributes that are to be casted.
     *
     * @var array
     */
    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the navigation items for the navigation.
     */
    public function items(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'navigation_id')->where('is_enabled', 1);
    }
}
