<?php

namespace Vektor\Pages\Http\View\Composers;

use Illuminate\Http\Request;
use Illuminate\View\View;
use Vektor\Pages\Models\Page;

class PageComposer
{
    /**
     * The request object.
     *
     * @var Request
     */
    protected $request;

    /**
     * Create a new page composer.
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Bind data to the view.
     */
    public function compose(View $view)
    {
        try {
            $page = Page::where('slug', $this->request->path())->first();
            if ($page) {
                $view->with('page', $page);
            }
        } catch (\Exception $e) {
        }
    }
}
