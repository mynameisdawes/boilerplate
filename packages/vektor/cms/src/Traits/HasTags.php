<?php

namespace Vektor\CMS\Traits;

use Vektor\CMS\Models\Tag;

trait HasTags
{
    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable')->withTimestamps();
    }
}
