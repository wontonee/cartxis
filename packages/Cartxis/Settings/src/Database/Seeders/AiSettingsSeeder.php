<?php

declare(strict_types=1);

namespace Cartxis\Settings\Database\Seeders;

use Illuminate\Database\Seeder;
use Cartxis\Core\Models\Setting;

class AiSettingsSeeder extends Seeder
{
    private const PRODUCT_DESCRIPTION_AGENT = 'Product Description Agent';
    private const PRICE_COMPARISON_AGENT = 'Price Comparison Agent';

    public function run(): void
    {
        $agentsSetting = Setting::where('key', 'ai.agents')->first();
        $agentsValue = $agentsSetting?->value;

        if (is_string($agentsValue)) {
            $agents = json_decode($agentsValue, true);
            $agents = is_array($agents) ? $agents : [];
        } elseif (is_array($agentsValue)) {
            $agents = $agentsValue;
        } else {
            $agents = [];
        }

        $hasProductDescriptionAgent = collect($agents)->contains(fn ($agent) => ($agent['name'] ?? '') === self::PRODUCT_DESCRIPTION_AGENT);
        $hasPriceComparisonAgent = collect($agents)->contains(fn ($agent) => ($agent['name'] ?? '') === self::PRICE_COMPARISON_AGENT);

        $agents = collect($agents)
            ->map(function ($agent) {
                if (!is_array($agent)) {
                    return $agent;
                }

                $name = (string) ($agent['name'] ?? '');
                $agent['is_system'] = in_array($name, [self::PRODUCT_DESCRIPTION_AGENT, self::PRICE_COMPARISON_AGENT], true);

                return $agent;
            })
            ->all();

        if (!$hasProductDescriptionAgent) {
            $agents[] = [
                'name' => self::PRODUCT_DESCRIPTION_AGENT,
                'provider' => '',
                'model' => '',
                'temperature' => 0.7,
                'max_tokens' => 1024,
                'system_prompt' => 'You are an AI product description generator for an e-commerce catalog. Use only the provided product data. Never invent facts or specs. If data is missing, omit it. Write in the requested tone and language. Output JSON with: short_description (50-100 words), long_description (200-500 words), meta_description (150-160 chars), bullet_points (5-8), keywords (array), tone_variations (object, optional), confidence_score (0-1), generation_timestamp (ISO 8601). Ensure SEO-friendly phrasing, clear benefits, and consistent brand voice. Avoid banned or unsafe claims.',
                'is_default' => false,
                'is_system' => true,
            ];
        }

        if (!$hasPriceComparisonAgent) {
            $agents[] = [
                'name' => self::PRICE_COMPARISON_AGENT,
                'provider' => '',
                'model' => '',
                'temperature' => 0.6,
                'max_tokens' => 800,
                'system_prompt' => 'You are an ecommerce pricing analyst. Based on product name, category, and store country, produce exactly 4 pricing options as JSON only. Each option must include variation, source_label, price, and details. Do not output markdown.',
                'is_default' => false,
                'is_system' => true,
            ];
        }

        Setting::updateOrCreate(
            ['key' => 'ai.agents'],
            [
                'value' => json_encode(array_values($agents)),
                'type' => 'json',
                'group' => 'ai',
            ]
        );

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

        if (!Setting::where('key', 'ai.price_comparison_agent')->exists()) {
            Setting::updateOrCreate(
                ['key' => 'ai.price_comparison_agent'],
                [
                    'value' => 'Price Comparison Agent',
                    'type' => 'string',
                    'group' => 'ai',
                ]
            );
        }
    }
}
