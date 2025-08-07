<?php

namespace Vektor\CMS\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'meta_title',
        'meta_description',
        'meta_image',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'href',
        'formatted_meta_description',
        'formatted_meta_image',
    ];

    public function getHrefAttribute()
    {
        $href = null;
        if (!empty($this->attributes['slug'])) {
            $href = url("[cms_entity]/tags/{$this->attributes['slug']}");
        }

        return $href;
    }

    public function getFormattedMetaDescriptionAttribute()
    {
        $formatted_meta_description = null;
        if (!empty($this->attributes['meta_description'])) {
            $formatted_meta_description = $this->attributes['meta_description'];
        }

        return $formatted_meta_description;
    }

    public function getFormattedMetaImageAttribute()
    {
        $formatted_meta_image = null;
        if (!empty($this->attributes['meta_image'])) {
            $formatted_meta_image = url(Storage::url($this->attributes['meta_image']));
        }

        return $formatted_meta_image;
    }

    public function taggable()
    {
        return $this->morphTo();
    }
}
