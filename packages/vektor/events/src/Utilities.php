<?php

namespace Vektor\Events;

use Vektor\Events\Models\Event;

class Utilities
{
    public static function eventLink($id)
    {
        $event = Event::find($id);
        if ($event && $event->href && !empty($event->href)) {
            return $event->href;
        }

        return '';
    }

    public static function eventTitle($id)
    {
        $event = Event::find($id);
        if ($event && $event->title && !empty($event->title)) {
            return $event->title;
        }

        return '';
    }
}
