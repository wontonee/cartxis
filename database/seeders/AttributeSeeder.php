<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttributeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // Define attributes with their options
        $attributes = [
            [
                'code' => 'color',
                'name' => 'Color',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => true,
                'sort_order' => 1,
                'options' => [
                    ['label' => 'Red', 'value' => 'red', 'swatch_value' => '#FF0000', 'sort_order' => 1],
                    ['label' => 'Blue', 'value' => 'blue', 'swatch_value' => '#0000FF', 'sort_order' => 2],
                    ['label' => 'Green', 'value' => 'green', 'swatch_value' => '#00FF00', 'sort_order' => 3],
                    ['label' => 'Black', 'value' => 'black', 'swatch_value' => '#000000', 'sort_order' => 4],
                    ['label' => 'White', 'value' => 'white', 'swatch_value' => '#FFFFFF', 'sort_order' => 5],
                    ['label' => 'Yellow', 'value' => 'yellow', 'swatch_value' => '#FFFF00', 'sort_order' => 6],
                    ['label' => 'Orange', 'value' => 'orange', 'swatch_value' => '#FFA500', 'sort_order' => 7],
                    ['label' => 'Purple', 'value' => 'purple', 'swatch_value' => '#800080', 'sort_order' => 8],
                    ['label' => 'Pink', 'value' => 'pink', 'swatch_value' => '#FFC0CB', 'sort_order' => 9],
                    ['label' => 'Gray', 'value' => 'gray', 'swatch_value' => '#808080', 'sort_order' => 10],
                ],
            ],
            [
                'code' => 'size',
                'name' => 'Size',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => true,
                'sort_order' => 2,
                'options' => [
                    ['label' => 'Extra Small (XS)', 'value' => 'xs', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => 'Small (S)', 'value' => 's', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => 'Medium (M)', 'value' => 'm', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => 'Large (L)', 'value' => 'l', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => 'Extra Large (XL)', 'value' => 'xl', 'swatch_value' => null, 'sort_order' => 5],
                    ['label' => '2XL', 'value' => '2xl', 'swatch_value' => null, 'sort_order' => 6],
                    ['label' => '3XL', 'value' => '3xl', 'swatch_value' => null, 'sort_order' => 7],
                ],
            ],
            [
                'code' => 'material',
                'name' => 'Material',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 3,
                'options' => [
                    ['label' => 'Cotton', 'value' => 'cotton', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => 'Polyester', 'value' => 'polyester', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => 'Wool', 'value' => 'wool', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => 'Leather', 'value' => 'leather', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => 'Silk', 'value' => 'silk', 'swatch_value' => null, 'sort_order' => 5],
                    ['label' => 'Denim', 'value' => 'denim', 'swatch_value' => null, 'sort_order' => 6],
                    ['label' => 'Linen', 'value' => 'linen', 'swatch_value' => null, 'sort_order' => 7],
                    ['label' => 'Nylon', 'value' => 'nylon', 'swatch_value' => null, 'sort_order' => 8],
                ],
            ],
            [
                'code' => 'brand',
                'name' => 'Brand',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 4,
                'options' => [
                    ['label' => 'Nike', 'value' => 'nike', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => 'Adidas', 'value' => 'adidas', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => 'Puma', 'value' => 'puma', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => 'Reebok', 'value' => 'reebok', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => 'Under Armour', 'value' => 'under_armour', 'swatch_value' => null, 'sort_order' => 5],
                ],
            ],
            [
                'code' => 'shoe_size',
                'name' => 'Shoe Size',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => true,
                'sort_order' => 5,
                'options' => [
                    ['label' => 'US 6', 'value' => 'us_6', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => 'US 7', 'value' => 'us_7', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => 'US 8', 'value' => 'us_8', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => 'US 9', 'value' => 'us_9', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => 'US 10', 'value' => 'us_10', 'swatch_value' => null, 'sort_order' => 5],
                    ['label' => 'US 11', 'value' => 'us_11', 'swatch_value' => null, 'sort_order' => 6],
                    ['label' => 'US 12', 'value' => 'us_12', 'swatch_value' => null, 'sort_order' => 7],
                ],
            ],
            [
                'code' => 'warranty',
                'name' => 'Warranty Period',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 6,
                'options' => [
                    ['label' => '6 Months', 'value' => '6_months', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => '1 Year', 'value' => '1_year', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => '2 Years', 'value' => '2_years', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => '3 Years', 'value' => '3_years', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => '5 Years', 'value' => '5_years', 'swatch_value' => null, 'sort_order' => 5],
                    ['label' => 'Lifetime', 'value' => 'lifetime', 'swatch_value' => null, 'sort_order' => 6],
                ],
            ],
            [
                'code' => 'waterproof',
                'name' => 'Waterproof',
                'type' => 'boolean',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 7,
                'options' => [],
            ],
            [
                'code' => 'weight',
                'name' => 'Weight',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_configurable' => false,
                'sort_order' => 8,
                'options' => [],
            ],
            [
                'code' => 'dimensions',
                'name' => 'Dimensions',
                'type' => 'text',
                'is_required' => false,
                'is_filterable' => false,
                'is_configurable' => false,
                'sort_order' => 9,
                'options' => [],
            ],
            [
                'code' => 'country_of_origin',
                'name' => 'Country of Origin',
                'type' => 'select',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 10,
                'options' => [
                    ['label' => 'USA', 'value' => 'usa', 'swatch_value' => null, 'sort_order' => 1],
                    ['label' => 'China', 'value' => 'china', 'swatch_value' => null, 'sort_order' => 2],
                    ['label' => 'Vietnam', 'value' => 'vietnam', 'swatch_value' => null, 'sort_order' => 3],
                    ['label' => 'Bangladesh', 'value' => 'bangladesh', 'swatch_value' => null, 'sort_order' => 4],
                    ['label' => 'India', 'value' => 'india', 'swatch_value' => null, 'sort_order' => 5],
                    ['label' => 'Italy', 'value' => 'italy', 'swatch_value' => null, 'sort_order' => 6],
                    ['label' => 'Germany', 'value' => 'germany', 'swatch_value' => null, 'sort_order' => 7],
                ],
            ],
            [
                'code' => 'care_instructions',
                'name' => 'Care Instructions',
                'type' => 'textarea',
                'is_required' => false,
                'is_filterable' => false,
                'is_configurable' => false,
                'sort_order' => 11,
                'options' => [],
            ],
            [
                'code' => 'eco_friendly',
                'name' => 'Eco-Friendly',
                'type' => 'boolean',
                'is_required' => false,
                'is_filterable' => true,
                'is_configurable' => false,
                'sort_order' => 12,
                'options' => [],
            ],
        ];

        // Insert attributes and their options
        foreach ($attributes as $attributeData) {
            $options = $attributeData['options'];
            unset($attributeData['options']);

            $attributeId = DB::table('attributes')->insertGetId([
                'code' => $attributeData['code'],
                'name' => $attributeData['name'],
                'type' => $attributeData['type'],
                'is_required' => $attributeData['is_required'],
                'is_filterable' => $attributeData['is_filterable'],
                'is_configurable' => $attributeData['is_configurable'],
                'sort_order' => $attributeData['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // Insert options if any
            if (!empty($options)) {
                $optionsToInsert = array_map(function ($option) use ($attributeId, $now) {
                    return [
                        'attribute_id' => $attributeId,
                        'label' => $option['label'],
                        'value' => $option['value'],
                        'swatch_value' => $option['swatch_value'],
                        'sort_order' => $option['sort_order'],
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }, $options);

                DB::table('attribute_options')->insert($optionsToInsert);
            }
        }

        $this->command->info('✓ Created ' . count($attributes) . ' attributes with their options');
    }
}
