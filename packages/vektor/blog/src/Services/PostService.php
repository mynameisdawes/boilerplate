<?php

namespace Vektor\Blog\Services;

use Illuminate\Http\Request;
use Vektor\Blog\Models\Post;
use Vektor\Blog\Models\PostCategory;

class PostService
{
    protected $hidden_fields = [
        'content',
        'created_at',
        'deleted_at',
        'meta_description',
        'meta_image',
        'meta_title',
        'metadata',
        'publish_date',
        'scheduled_at',
        'slug',
        'sort_order',
        'status',
        'updated_at',
    ];

    protected $selected_fields = [
        'author',
        'content',
        'created_at',
        'id',
        'meta_description',
        'meta_image',
        'scheduled_at',
        'slug',
        'status',
        'title',
    ];

    public function index(Request $request)
    {
        try {
            $perPage = $request->input('per_page', 20);
            $page = $request->input('page', 1);
            $query = Post::whereStatus(Post::PUBLISHED)
                ->orderByDesc('scheduled_at')
                ->orderByDesc('created_at')
            ;

            if ($request->filled('category_ids')) {
                $category_ids = [];
                $post_categories = PostCategory::whereIn('id', (array) $request->input('category_ids'))->get();

                if ($post_categories->count() > 0) {
                    foreach ($post_categories as $post_category) {
                        $category_ids[] = $post_category->id;
                        $post_category_descendants = $post_category->descendants();
                        if ($post_category_descendants->count() > 0) {
                            foreach ($post_category_descendants as $post_category_descendant) {
                                $category_ids[] = $post_category_descendant->id;
                            }
                        }
                    }
                }

                if (!empty($category_ids)) {
                    $category_ids = array_values(array_unique($category_ids));
                    $query->whereHas('categories', function ($query) use ($category_ids) {
                        $query->whereIn('post_categories.id', $category_ids);
                    });
                }
            }

            if ($request->filled('tag_ids')) {
                $query->whereHas('tags', function ($query) use ($request) {
                    $query->whereIn('tags.id', (array) $request->input('tag_ids'));
                });
            }

            if ($request->input('paginate', false)) {
                if ($request->filled('id')) {
                    $ids = $query->pluck('id')->toArray();
                    $index = array_search($request->input('id'), $ids);
                    if (false !== $index) {
                        $page = floor($index / $perPage) + 1;
                    }
                }

                $posts = $query
                    ->select($this->selected_fields)
                    ->paginate($perPage, ['*'], 'page', $page)
                    ->through(function ($post) {
                        return $post->makeHidden($this->hidden_fields);
                    })
                ;
            } else {
                $posts = $query
                    ->select($this->selected_fields)
                    ->get()
                    ->makeHidden($this->hidden_fields)
                ;
            }

            return [
                'success' => true,
                'data' => [
                    'posts' => $posts,
                ],
            ];
        } catch (\Throwable $th) {
            return [
                'error' => true,
                'error_message' => $th->getMessage(),
                'http_code' => $th->getCode(),
            ];
        }
    }
}
