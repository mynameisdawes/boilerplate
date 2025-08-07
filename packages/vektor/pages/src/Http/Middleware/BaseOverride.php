<?php

namespace Vektor\Pages\Http\Middleware;

use Illuminate\Http\Request;
use Vektor\Pages\Models\Page;

class BaseOverride
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, \Closure $next)
    {
        try {
            $cms_entity = Page::whereStatus(Page::PUBLISHED)->whereIn('slug', ['', '/'])->first();

            if ($cms_entity) {
                if (!empty($cms_entity->content)) {
                    $cms_entity->title = null;

                    return response()->view('pages::show', ['cms_entity' => $cms_entity, 'cms_type' => 'page']);
                }
            }
        } catch (\Exception $e) {
        }

        return $next($request);
    }
}
