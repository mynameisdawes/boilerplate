<?php

namespace Vektor\CMS\Database\Seeders;

use Illuminate\Database\Seeder;
use Vektor\CMS\Models\Navigation;
use Vektor\CMS\Models\NavigationItem;

class NavigationSeeder extends Seeder
{
    /**
     * Seed the application"s database.
     */
    public function run(): void
    {
        $navigation_header = Navigation::create([
            'title' => 'header',
            'is_enabled' => true,
        ]);

        $navigation_subfooter = Navigation::create([
            'title' => 'subfooter',
            'is_enabled' => true,
        ]);

        NavigationItem::create([
            'navigation_id' => $navigation_subfooter->id,
            'title' => 'Terms & Conditions',
            'slug' => 'terms',
        ]);

        NavigationItem::create([
            'navigation_id' => $navigation_subfooter->id,
            'title' => 'Privacy Policy',
            'slug' => 'policy',
        ]);
    }
}
