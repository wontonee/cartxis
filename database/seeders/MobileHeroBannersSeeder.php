<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\CMS\Models\Block;

class MobileHeroBannersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $banners = [
            [
                'identifier' => 'mobile-home-hero-summer-sale',
                'title' => 'Summer Sale',
                'type' => 'banner',
                'status' => 'active',
                'content' => [
                    'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuDZOJapra7fy6BAM2iHLk9hSxtELNcnNARwEiqlK9sD3o8I0_A38Nqual6NZ4N8pmVIQBJJkPLpu0pHB_8eLIzvEqMzvXxOTDxx0uLfV2sF1P-T1TMv_Py-B1ZP-hM8hLRF2BbBiKCmmxC3weWN0rTgwAoP8UJPklNM_GKD_cD2O8qWl_GGRfF5eMytCo8ZbG39l_a-OdMb7C-HdSyU7CCzshPBlzcXJtEGjw9nIhbW5Qwwny5mSKL1h5fiDv-ofgkeG95o4Y-J9hWm',
                    'label' => 'PROMO',
                    'subtitle' => 'Up to 50% Off Electronics',
                    // Flutter: Colors.black.withOpacity(0.6)
                    'overlay_color' => '#00000099',
                ],
            ],
            [
                'identifier' => 'mobile-home-hero-fashion-week',
                'title' => 'Fashion Week',
                'type' => 'banner',
                'status' => 'active',
                'content' => [
                    'image' => 'https://lh3.googleusercontent.com/aida-public/AB6AXuCy0yJAkZq9Kf7iSGQAHsL1hW-VddU370wK0zu9ElzV0TGfbGcKLLu9mXoHSNxRBSmsXw-QsQ1UeC0MKs6N0r0Roa4WiGiAsmwsuF8Cou15aV0s8HS-dmH80W0RuHAGRj84j9R0jvSNjxII0zTuNUQGrTuXlGtUsEY8iiMMQUBoPYQxPylkdJuSRG85fUtX9jf-9YoFPbaFOTIXydlPjjcY6h9dKmziCjJOBlOGuaaftHPrNW6jkX1tR5d2xBvvYdEBmSFfRBHZRbK_',
                    'label' => 'NEW',
                    'subtitle' => 'Trending Styles 2023',
                    // Flutter: const Color(0xFF581C87).withOpacity(0.6)
                    'overlay_color' => '#581C8799',
                ],
            ],
        ];

        foreach ($banners as $banner) {
            Block::updateOrCreate(
                ['identifier' => $banner['identifier']],
                [
                    'title' => $banner['title'],
                    'type' => $banner['type'],
                    'status' => $banner['status'],
                    'content' => json_encode($banner['content'], JSON_UNESCAPED_SLASHES),
                ]
            );
        }

        $this->command?->info('Mobile hero banners seeded: ' . count($banners));
    }
}
