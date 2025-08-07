<?php

namespace Vektor\Blog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Vektor\Blog\Models\Post;

class BlogPostPublish extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cms:post:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks if any unpublished blog posts need to published.';

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
        $posts = Post::where('status', Post::SCHEDULED)->whereDate('scheduled_at', '>=', Carbon::now());
        $posts->update(['status' => Post::PUBLISHED]);
    }
}
