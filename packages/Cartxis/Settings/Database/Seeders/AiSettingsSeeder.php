<?php

declare(strict_types=1);

namespace Cartxis\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\Setting;

class AiSettingsSeeder extends Seeder
{
    public function run(): void
    {
        if (!Setting::where('key', 'ai.agents')->exists()) {
            Setting::updateOrCreate(
                ['key' => 'ai.agents'],
                [
                    'value' => json_encode([
                        [
                            'name' => 'Product Description Agent',
                            'provider' => '',
                            'model' => '',
                            'temperature' => 0.7,
                            'max_tokens' => 1024,
                            'system_prompt' => 'You are an AI product description generator for an e-commerce catalog. Use only the provided product data. Never invent facts or specs. If data is missing, omit it. Write in the requested tone and language. Output JSON with: short_description (50-100 words), long_description (200-500 words), meta_description (150-160 chars), bullet_points (5-8), keywords (array), tone_variations (object, optional), confidence_score (0-1), generation_timestamp (ISO 8601). Ensure SEO-friendly phrasing, clear benefits, and consistent brand voice. Avoid banned or unsafe claims.',
                            'is_default' => false,
                        ],
                    ]),
                    'type' => 'json',
                    'group' => 'ai',
                ]
            );
        }

        if (!Setting::where('key', 'ai.product_description_agent')->exists()) {
            Setting::updateOrCreate(
                ['key' => 'ai.product_description_agent'],
                [
                    'value' => 'Product Description Agent',
                    'type' => 'string',
                    'group' => 'ai',
                ]
            );
        }
    }
}
