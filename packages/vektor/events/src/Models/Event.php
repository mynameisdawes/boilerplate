<?php

namespace Vektor\Events\Models;

use Datetime as PHPDateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Event extends Model
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
        'date',
        'time_start',
        'time_end',
        'type',
        'description',
        'performance_title',
        'performance_href',
        'is_featured',
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
        'date' => 'date',
        'is_featured' => 'boolean',
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
        'excerpt',
        'formatted_description',
        'formatted_group',
        'formatted_date',
        'formatted_time',
        'is_today',
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

    public function formatEventTime($time)
    {
        $dateTime = PHPDateTime::createFromFormat('H:i:s', $time);

        return $dateTime ? $dateTime->format('g:ia') : false;
    }

    public function getHrefAttribute()
    {
        $href = null;
        if (!empty($this->attributes['slug'])) {
            $href = url("events/{$this->attributes['slug']}");
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
            if (!empty($this->attributes['description'])) {
                $formatted_meta_description = Str::words(strip_tags($this->attributes['description']), 20);
            }
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

    public function getExcerptAttribute()
    {
        $excerpt = null;
        if (!empty($this->attributes['description'])) {
            $excerpt = Str::words(strip_tags($this->attributes['description']), 50);
        }

        return $excerpt;
    }

    public function getFormattedDescriptionAttribute()
    {
        $formatted_description = null;
        if (!empty($this->attributes['description'])) {
            $formatted_description = nl2br($this->attributes['description']);
        }

        return $formatted_description;
    }

    public function getFormattedGroupAttribute()
    {
        $formatted_group = Carbon::parse($this->attributes['date']);
        if ($formatted_group) {
            return $formatted_group->format('F Y');
        }

        return null;
    }

    public function getFormattedDateAttribute()
    {
        $formatted_date = Carbon::parse($this->attributes['date']);
        if ($formatted_date) {
            return $formatted_date->format('j\<\s\u\p\>S\<\/\s\u\p\> F Y');
        }

        return null;
    }

    public function getFormattedTimeAttribute()
    {
        $formatted_time = null;
        $time_start = $this->attributes['time_start'];
        if (!empty($time_start)) {
            $formatted_time = $this->formatEventTime($time_start);
            $time_end = $this->attributes['time_end'];
            if (!empty($time_end)) {
                $formatted_time .= ' - '.$this->formatEventTime($time_end);
            }
        }

        return $formatted_time;
    }

    public function getIsTodayAttribute()
    {
        $date = Carbon::parse($this->attributes['date']);
        if ($date) {
            return $date->isToday();
        }

        return false;
    }
}
