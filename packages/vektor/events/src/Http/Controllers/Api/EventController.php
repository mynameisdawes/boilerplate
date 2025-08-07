<?php

namespace Vektor\Events\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Events\Models\Event;

class EventController extends ApiController
{
    protected $hidden_fields = [
        'created_at',
        'date',
        'deleted_at',
        'description',
        'excerpt',
        'formatted_meta_description',
        'formatted_publish_date',
        'href',
        'meta_description',
        'meta_image',
        'meta_title',
        'publish_date',
        'scheduled_at',
        'slug',
        'sort_order',
        'status',
        'time_end',
        'time_start',
        'type',
        'updated_at',
    ];

    protected $selected_fields = [
        'created_at',
        'date',
        'description',
        'id',
        'is_featured',
        'meta_image',
        'performance_href',
        'performance_title',
        'scheduled_at',
        'slug',
        'status',
        'time_end',
        'time_start',
        'title',
        'type',
    ];

    public function past(Request $request)
    {
        try {
            $past_event_query = Event::whereStatus(Event::PUBLISHED)->select($this->selected_fields)->whereDate('date', '<', Carbon::today())->orderByDesc('date')->orderByDesc('time_start')->orderByDesc('time_end');

            if ($request->input('paginate', false)) {
                $past_events = $past_event_query->paginate($request->input('per_page', 20))->through(function ($event) {
                    return $event->makeHidden($this->hidden_fields);
                });
            } else {
                $past_events = $past_event_query->get()->makeHidden($this->hidden_fields);
            }

            return $this->response([
                'success' => true,
                'data' => [
                    'past_events' => $past_events,
                ],
            ]);
        } catch (\Throwable $th) {
            return $this->response([
                'error' => true,
                'error_message' => $th->getMessage(),
                'http_code' => $th->getCode(),
            ]);
        }
    }

    public function upcoming(Request $request)
    {
        try {
            $upcoming_event_query = Event::whereStatus(Event::PUBLISHED)->select($this->selected_fields)->whereDate('date', '>=', Carbon::today())->orderBy('date')->orderBy('time_start')->orderBy('time_end');

            $upcoming_featured_event_query = Event::whereStatus(Event::PUBLISHED)->select($this->selected_fields)->whereDate('date', '>=', Carbon::today())->where('is_featured', '1')->orderBy('date')->orderBy('time_start')->orderBy('time_end')->take(1);

            $upcoming_featured_events = $upcoming_featured_event_query->get()->makeHidden($this->hidden_fields);

            $upcoming_featured_events_ids = $upcoming_featured_events->pluck('id');

            if ($upcoming_featured_events_ids->count() > 0) {
                $upcoming_event_query->whereNotIn('id', $upcoming_featured_events_ids);
            }

            if ($request->input('paginate', false)) {
                $upcoming_events = $upcoming_event_query->paginate($request->input('per_page', 20))->through(function ($event) {
                    return $event->makeHidden($this->hidden_fields);
                });
            } else {
                $upcoming_events = $upcoming_event_query->get()->makeHidden($this->hidden_fields);
            }

            return $this->response([
                'success' => true,
                'data' => [
                    'upcoming_featured_events' => $upcoming_featured_events,
                    'upcoming_events' => $upcoming_events,
                ],
            ]);
        } catch (\Throwable $th) {
            return $this->response([
                'error' => true,
                'error_message' => $th->getMessage(),
                'http_code' => $th->getCode(),
            ]);
        }
    }
}
