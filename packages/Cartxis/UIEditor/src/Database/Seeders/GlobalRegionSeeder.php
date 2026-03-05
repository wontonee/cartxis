<?php

declare(strict_types=1);

namespace Cartxis\UIEditor\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\UIEditor\Models\GlobalRegion;

class GlobalRegionSeeder extends Seeder
{
    /**
     * Seed default global regions (header + footer).
     * Idempotent — skips any region whose slug already exists.
     */
    public function run(): void
    {
        $regions = [
            [
                'name'        => 'Main Header',
                'slug'        => 'main-header',
                'description' => 'Site-wide header with logo, navigation and cart.',
                'region_type' => 'header',
                'status'      => 'draft',
                'layout_data' => json_encode([
                    'version'  => '1.0',
                    'sections' => [
                        [
                            'id'       => 'sec_header_1',
                            'type'     => 'section',
                            'settings' => [
                                'background_color' => '#ffffff',
                                'padding_top'      => 0,
                                'padding_bottom'   => 0,
                                'full_width'       => true,
                            ],
                            'columns' => [
                                [
                                    'id'       => 'col_header_1',
                                    'width'    => 12,
                                    'settings' => [],
                                    'blocks'   => [
                                        [
                                            'id'       => 'blk_header_1',
                                            'type'     => 'heading',
                                            'settings' => [
                                                'level' => 'h1',
                                                'text'  => 'My Store',
                                                'align' => 'center',
                                                'color' => '#111827',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
            [
                'name'        => 'Main Footer',
                'slug'        => 'main-footer',
                'description' => 'Site-wide footer with links, newsletter and copyright.',
                'region_type' => 'footer',
                'status'      => 'draft',
                'layout_data' => json_encode([
                    'version'  => '1.0',
                    'sections' => [
                        [
                            'id'       => 'sec_footer_1',
                            'type'     => 'section',
                            'settings' => [
                                'background_color' => '#0f172a',
                                'padding_top'      => 48,
                                'padding_bottom'   => 48,
                                'full_width'       => true,
                            ],
                            'columns' => [
                                [
                                    'id'       => 'col_footer_1',
                                    'width'    => 12,
                                    'settings' => [],
                                    'blocks'   => [
                                        [
                                            'id'       => 'blk_footer_brand',
                                            'type'     => 'heading',
                                            'settings' => [
                                                'level' => 'h3',
                                                'text'  => 'My Store',
                                                'align' => 'center',
                                                'color' => '#ffffff',
                                            ],
                                        ],
                                        [
                                            'id'       => 'blk_footer_tagline',
                                            'type'     => 'text',
                                            'settings' => [
                                                'content' => '<p style="text-align:center;color:rgba(255,255,255,0.55)">Your trusted e-commerce platform.</p>',
                                            ],
                                        ],
                                        [
                                            'id'       => 'blk_footer_copy',
                                            'type'     => 'text',
                                            'settings' => [
                                                'content' => '<p style="text-align:center;color:rgba(255,255,255,0.35);font-size:12px">&copy; ' . date('Y') . ' My Store. All rights reserved.</p>',
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]),
            ],
        ];

        foreach ($regions as $data) {
            // Skip if already exists (idempotent)
            if (GlobalRegion::where('slug', $data['slug'])->exists()) {
                continue;
            }

            // layout_data is already JSON-encoded above; decode for the model
            // because the model will cast it back to array/JSON on save
            $data['layout_data'] = json_decode($data['layout_data'], true);

            GlobalRegion::create($data);
        }
    }
}
