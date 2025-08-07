<?php

namespace Vektor\Pages;

use Vektor\Pages\Models\Page;

class Utilities
{
    public static function pageLink($id)
    {
        $page = Page::find($id);
        if ($page && $page->href && !empty($page->href)) {
            return $page->href;
        }

        return '';
    }

    public static function pageTitle($id)
    {
        $page = Page::find($id);
        if ($page && $page->title && !empty($page->title)) {
            return $page->title;
        }

        return '';
    }
}
