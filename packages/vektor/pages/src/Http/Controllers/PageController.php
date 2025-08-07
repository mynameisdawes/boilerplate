<?php

namespace Vektor\Pages\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Pages\Models\Page;

class PageController extends ApiController
{
    public function show(Request $request, $slug = '/')
    {
        $cms_entity = Page::whereStatus(Page::PUBLISHED)->whereSlug($slug)->first();

        if ($cms_entity) {
            if (!empty($cms_entity->content)) {
                return view('pages::show', ['cms_entity' => $cms_entity, 'cms_type' => 'page']);
            }
        }

        abort(404);
    }
}
