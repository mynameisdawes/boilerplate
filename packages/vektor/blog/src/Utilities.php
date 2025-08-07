<?php

namespace Vektor\Blog;

use Vektor\Blog\Models\Post;

class Utilities
{
    public static function postLink($id)
    {
        $post = Post::find($id);
        if ($post && $post->href && !empty($post->href)) {
            return $post->href;
        }

        return '';
    }

    public static function postTitle($id)
    {
        $post = Post::find($id);
        if ($post && $post->title && !empty($post->title)) {
            return $post->title;
        }

        return '';
    }
}
