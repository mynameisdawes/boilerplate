<?php

namespace Vektor\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\CMS\Models\Tag;

class PostTagController extends ApiController
{
    public function show(Request $request, $slug)
    {
        $cms_entity = Tag::where('slug', $slug)->first();

        if ($cms_entity) {
            return view('posts::tags.show', ['cms_entity' => $cms_entity, 'cms_type' => 'post_tag']);
        }

        abort(404);
    }
}
