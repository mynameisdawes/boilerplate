<?php

namespace Vektor\Events\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Events\Models\Event;

class EventController extends ApiController
{
    public function show(Request $request, $slug = '/')
    {
        $cms_entity = Event::whereStatus(Event::PUBLISHED)->whereSlug($slug)->first();

        if ($cms_entity) {
            if (!empty($cms_entity->content)) {
                return view('events::show', ['cms_entity' => $cms_entity, 'cms_type' => 'event']);
            }
        }

        abort(404);
    }
}
