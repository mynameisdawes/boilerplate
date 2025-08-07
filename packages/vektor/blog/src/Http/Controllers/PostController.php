<?php

namespace Vektor\Blog\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Blog\Models\Post;
use Vektor\Blog\Models\PostCategory;

class PostController extends ApiController
{
    public function show(Request $request, $first_slug, $second_slug = null)
    {
        if (is_null($second_slug)) {
            $slug = $first_slug;

            $cms_entity = Post::whereStatus(Post::PUBLISHED)->whereSlug($slug)->first();
        } else {
            $category_path = $first_slug;
            $slug = $second_slug;

            $category = PostCategory::findByPath($category_path);

            $cms_entity = Post::whereStatus(Post::PUBLISHED)->whereSlug($slug)->whereHas('categories', function ($query) use ($category) { $query->where('post_categories.id', $category->id); })->first();
        }

        if ($cms_entity) {
            if (!empty($cms_entity->content)) {
                return view('posts::show', ['cms_entity' => $cms_entity, 'cms_type' => 'post']);
            }
        }

        abort(404);
    }
}
