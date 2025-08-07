<?php

namespace Vektor\Blog\Models;

use Illuminate\Database\Eloquent\Model;

class PostCategory extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'slug',
        'parent_id',
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
            $href = url("blog/categories/{$this->attributes['slug']}");
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

    public function posts()
    {
        return $this->belongsToMany(Post::class)->withTimestamps();
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function findByPath(string $path)
    {
        $slugs = explode('/', trim($path, '/'));

        $query = static::where('slug', array_pop($slugs));
        $parent = null;

        while ($slugs) {
            $slug = array_pop($slugs);
            $parent = static::where('slug', $slug)->where('parent_id', $parent?->id)->firstOrFail();
            $query = $query->where('parent_id', $parent->id);
        }

        return $query->firstOrFail();
    }

    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    public function descendants()
    {
        $descendants = collect();
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->descendants());
        }

        return $descendants;
    }
}
