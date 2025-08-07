<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Vektor\Blog\Services\PostService;

class PageController extends Controller
{
    public function base(Request $request)
    {
        if (true === config('shop.as_base') || true === config('shop.only')) {
            if (config('shop.single_product_slug')) {
                return redirect()->route('shop.product.show', config('shop.single_product_slug'));
            }

            return view('shop::index');
        }

        return view('base');
    }

    public function about(Request $request)
    {
        if (false === config('shop.as_base') || true === config('shop.only')) {
            return redirect()->route('base');
        }

        return view('base');
    }

    public function tabs(Request $request)
    {
        return view('tabs');
    }

    public function article(Request $request)
    {
        return view('article');
    }

    public function map(Request $request)
    {
        return view('map');
    }

    public function contact(Request $request)
    {
        return view('contact');
    }

    public function test(Request $request)
    {
        return view('test');
    }

    public function terms(Request $request)
    {
        return view('terms');
    }

    public function policy(Request $request)
    {
        return view('policy');
    }

    public function cards(Request $request)
    {
        $paginate = true;
        $initial_posts = [];
        $initial_page = (int) $request->input('page', 1);
        $last_page = 1;
        $per_page = (int) $request->input('per_page', 3);

        $post_id = $request->input('post_id', $request->session()->get('post_id'));

        $request_payload = [
            'paginate' => $paginate,
            'per_page' => $per_page,
            'page' => $initial_page,
        ];

        if ($post_id) {
            $request_payload['id'] = (int) $post_id;
            $request->session()->forget('post_id');
        }

        $request->merge($request_payload);

        $post_service = new PostService();
        $response = $post_service->index($request);

        if ($paginate) {
            if (isset($response['data'], $response['data']['posts'])) {
                $response_data = $response['data']['posts']->toArray();

                if (isset($response_data['data'])) {
                    $initial_posts = $response_data['data'];
                }

                if (isset($response_data['current_page'])) {
                    $initial_page = $response_data['current_page'];
                }

                if (isset($response_data['last_page'])) {
                    $last_page = $response_data['last_page'];
                }

                if (isset($response_data['per_page'])) {
                    $per_page = $response_data['per_page'];
                }
            }
        } else {
            if (isset($response['data'], $response['data']['posts'])) {
                $response_data = $response['data']['posts']->toArray();

                $initial_posts = $response_data;
            }
        }

        return view('cards.index', [
            'initial_posts' => $initial_posts,
            'initial_page' => $initial_page,
            'last_page' => $last_page,
            'per_page' => $per_page,
            'paginate' => $paginate,
        ]);
    }
}
