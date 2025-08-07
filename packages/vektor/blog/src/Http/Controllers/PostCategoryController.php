<?php

namespace Vektor\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Blog\Models\PostCategory;

class PostCategoryController extends ApiController
{
    public function show(Request $request, $category_path)
    {
        $cms_entity = PostCategory::findByPath($category_path);

        if ($cms_entity) {
            return view('posts::categories.show', ['cms_entity' => $cms_entity, 'cms_type' => 'post_category']);
        }

        abort(404);
    }
}
