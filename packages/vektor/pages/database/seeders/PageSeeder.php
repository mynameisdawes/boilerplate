<?php

namespace Vektor\Pages\Database\Seeders;

use Illuminate\Database\Seeder;
use Vektor\Pages\Models\Page;

class PageSeeder extends Seeder
{
    /**
     * Seed the application"s database.
     */
    public function run(): void
    {
        $markdown_terms = file_get_contents(__DIR__.'/../../resources/markdown/terms.md');

        Page::create([
            'title' => 'Terms & Conditions',
            'slug' => 'terms',
            'content' => [
                [
                    'layout' => 'image_markdown',
                    'key' => 'image_markdown_'.uniqid(),
                    'attributes' => [
                        'container_width' => 'md',
                        'content_mode' => null,
                        'background_colour' => null,
                        'image/markdown' => [
                            [
                                'layout' => 'image_markdown',
                                'key' => 'image_markdown_'.uniqid(),
                                'attributes' => [
                                    'content_inline_alignment' => null,
                                    'content_block_alignment' => null,
                                    'content_position' => null,
                                    'image_behaviour' => null,
                                    'image_alt' => null,
                                    'markdown' => $markdown_terms,
                                    'buttons' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'status' => '2',
        ]);

        $markdown_policy = file_get_contents(__DIR__.'/../../resources/markdown/policy.md');

        Page::create([
            'title' => 'Privacy Policy',
            'slug' => 'policy',
            'content' => [
                [
                    'layout' => 'image_markdown',
                    'key' => 'image_markdown_'.uniqid(),
                    'attributes' => [
                        'container_width' => 'md',
                        'content_mode' => null,
                        'background_colour' => null,
                        'image/markdown' => [
                            [
                                'layout' => 'image_markdown',
                                'key' => 'image_markdown_'.uniqid(),
                                'attributes' => [
                                    'content_inline_alignment' => null,
                                    'content_block_alignment' => null,
                                    'content_position' => null,
                                    'image_behaviour' => null,
                                    'image_alt' => null,
                                    'markdown' => $markdown_policy,
                                    'buttons' => [],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'status' => '2',
        ]);
    }
}
