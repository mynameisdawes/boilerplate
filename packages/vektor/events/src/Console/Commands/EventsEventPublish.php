<?php

namespace Vektor\Events\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Vektor\Events\Models\Event;

class EventsEventPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:event:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if any unpublished events events need to published.';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $events = Event::where('status', Event::SCHEDULED)->whereDate('scheduled_at', '>=', Carbon::now());
        $events->update(['status' => Event::PUBLISHED]);
    }
}
