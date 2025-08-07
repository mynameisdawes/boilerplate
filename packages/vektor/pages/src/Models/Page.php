<?php

namespace Vektor\Pages\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Page extends Model
{
    use SoftDeletes;
    use HasSlug;

    public const DRAFT = 0;
    public const SCHEDULED = 1;
    public const PUBLISHED = 2;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'slug',
        'metadata',
        'content',
        'status',
        'sort_order',
        'scheduled_at',
        'meta_title',
        'meta_description',
        'meta_image',
    ];

    /**
     * The attributes that are to be casted.
     *
     * @var array
     */
    protected $casts = [
        'metadata' => 'array',
        'content' => 'array',
        'status' => 'integer',
        'sort_order' => 'integer',
        'scheduled_at' => 'date',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'href',
        'publish_date',
        'formatted_publish_date',
        'formatted_meta_description',
        'formatted_meta_image',
    ];

    /**
     * Get the options for generating the slug.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('title')
            ->saveSlugsTo('slug')
            ->preventOverwrite()
        ;
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function findFirstMatchingKeyValue(array $array, string $targetKey)
    {
        foreach ($array as $key => $value) {
            if ($key === $targetKey && !empty($value)) {
                return $value;
            }

            if (is_array($value)) {
                $result = $this->findFirstMatchingKeyValue($value, $targetKey);
                if (null !== $result) {
                    return $result;
                }
            }
        }

        return null;
    }

    public function getHrefAttribute()
    {
        $href = null;
        if (!empty($this->attributes['slug'])) {
            $href = url($this->attributes['slug']);
        }

        return $href;
    }

    public function getPublishDateAttribute()
    {
        $publish_date = Carbon::parse($this->attributes['created_at']);
        if (self::PUBLISHED == $this->attributes['status'] && !empty($this->attributes['scheduled_at'])) {
            $publish_date = Carbon::parse($this->attributes['scheduled_at']);
        }

        return $publish_date;
    }

    public function getFormattedPublishDateAttribute()
    {
        $publish_date = $this->getPublishDateAttribute();
        if ($publish_date) {
            return $publish_date->format('j\<\s\u\p\>S\<\/\s\u\p\> F Y');
        }

        return null;
    }

    public function getFormattedMetaDescriptionAttribute()
    {
        $formatted_meta_description = null;
        if (!empty($this->attributes['meta_description'])) {
            $formatted_meta_description = $this->attributes['meta_description'];
        } else {
            $content = json_decode($this->attributes['content'], true);
            $content_markdown = $this->findFirstMatchingKeyValue($content, 'markdown');
            if ($content_markdown) {
                $formatted_meta_description = Str::words(strip_tags($content_markdown), 20);
            }
        }

        return $formatted_meta_description;
    }

    public function getFormattedMetaImageAttribute()
    {
        $formatted_meta_image = null;
        if (!empty($this->attributes['meta_image'])) {
            $formatted_meta_image = url(Storage::url($this->attributes['meta_image']));
        } else {
            $content = json_decode($this->attributes['content'], true);
            $content_image = $this->findFirstMatchingKeyValue($content, 'image');
            if ($content_image) {
                $formatted_meta_image = url(Storage::url($content_image));
            }
        }

        return $formatted_meta_image;
    }
}
