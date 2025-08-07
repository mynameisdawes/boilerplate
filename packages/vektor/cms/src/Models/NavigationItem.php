<?php

namespace Vektor\CMS\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class NavigationItem extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'navigation_id',
        'parent_id',
        'linked_model_name',
        'linked_model_id',
        'title',
        'slug',
        'attributes',
        'is_enabled',
        'sort_order',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'navigation_id',
        'parent_id',
        'linked_model_name',
        'linked_model_id',
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
        'attributes' => 'array',
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'href',
    ];

    /**
     * Get the navigation that owns the navigation item.
     */
    public function navigation(): BelongsTo
    {
        return $this->belongsTo(Navigation::class, 'navigation_id');
    }

    /**
     * Get the parent navigation item that owns the navigation item.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(NavigationItem::class, 'parent_id');
    }

    /**
     * Get the child navigation items for the navigation item.
     */
    public function children(): HasMany
    {
        return $this->hasMany(NavigationItem::class, 'parent_id')->where('is_enabled', 1);
    }

    public function setAttributesAttribute($value)
    {
        $array = [];

        if (!empty($value) && is_array($value)) {
            foreach ($value as $array_key => $array_item) {
                if (!is_null($array_item)) {
                    $array[$array_key] = $array_item;
                }
            }
        }

        if (!empty($array)) {
            $this->attributes['attributes'] = json_encode($array);
        } else {
            $this->attributes['attributes'] = null;
        }
    }

    public function getHrefAttribute()
    {
        $href = null;
        if (!empty($this->attributes['slug'])) {
            $href = url($this->attributes['slug']);
        }
        if (class_exists($this->attributes['linked_model_name'])) {
            $model = app()->make($this->attributes['linked_model_name'])::find($this->attributes['linked_model_id']);
            if ($model && !empty($model->href)) {
                $href = $model->href;
            }
        }

        return $href;
    }
}
