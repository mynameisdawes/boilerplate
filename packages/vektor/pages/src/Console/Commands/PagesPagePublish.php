<?php

namespace Vektor\Pages\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Vektor\Pages\Models\Page;

class PagesPagePublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:page:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if any unpublished pages need to published.';

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
        $pages = Page::where('status', Page::SCHEDULED)->whereDate('scheduled_at', '>=', Carbon::now());
        $pages->update(['status' => Page::PUBLISHED]);
    }
}
