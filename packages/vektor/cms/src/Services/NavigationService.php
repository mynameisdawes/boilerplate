<?php

namespace Vektor\CMS\Services;

use Vektor\CMS\Models\Navigation;

class NavigationService
{
    public function fetch($navigation_title = null)
    {
        try {
            if ($navigation_title) {
                $navigation = Navigation::with(['items' => function ($query) {
                    $query->orderBy('sort_order')->whereNull('parent_id');
                }, 'items.children' => function ($query) {
                    $query->orderBy('sort_order');
                }])->where('title', $navigation_title)->where('is_enabled', 1)->first();

                return $navigation;
            }
        } catch (\Exception $e) {
        }

        return null;
    }
}
