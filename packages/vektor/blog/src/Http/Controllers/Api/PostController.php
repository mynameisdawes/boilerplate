<?php

namespace Vektor\Blog\Http\Controllers\Api;

use Illuminate\Http\Request;
use Vektor\Api\Http\Controllers\ApiController;
use Vektor\Blog\Services\PostService;

class PostController extends ApiController
{
    public function index(Request $request)
    {
        $post_service = new PostService();

        return $this->response($post_service->index($request));
    }
}
