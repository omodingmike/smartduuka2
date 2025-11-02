<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use App\Models\ProductVariation;
use Dipokhalder\EnvEditor\EnvEditor;

class ProductVariationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public array $fashionVariations = [
        [
            'product_attribute_id'        => 1,
            'product_attribute_option_id' => 1,
            'children'                    => [
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 6
                ],
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 7
                ],
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 8
                ]
            ]
        ],
        [
            'product_attribute_id'        => 1,
            'product_attribute_option_id' => 2,
            'children'                    => [
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 6
                ],
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 7
                ],
                [
                    'product_attribute_id'        => 2,
                    'product_attribute_option_id' => 8
                ]
            ]
        ]
    ];

    public function run(): void
    {
        $envService = new EnvEditor();
        if ($envService->getValue('DEMO') && $envService->getValue('DISPLAY') == 'fashion') {
            $products = Product::select('id', 'variation_price')->get();

            foreach ($products as $product) {
                foreach ($this->fashionVariations as $fashionVariation) {
                    $parent = ProductVariation::create([
                        'product_id'                  => $product->id,
                        'product_attribute_id'        => $fashionVariation['product_attribute_id'],
                        'product_attribute_option_id' => $fashionVariation['product_attribute_option_id'],
                        'price'                       => $product->variation_price,
                        'sku'                         => null,
                        'parent_id'                   => null
                    ]);

                    if (isset($fashionVariation['children'])) {
                        foreach ($fashionVariation['children'] as $childVariation) {
                            ProductVariation::create([
                                'product_id'                  => $product->id,
                                'product_attribute_id'        => $childVariation['product_attribute_id'],
                                'product_attribute_option_id' => $childVariation['product_attribute_option_id'],
                                'price'                       => $product->variation_price,
                                'sku'                         => date('is') . str_pad(rand(1, 999999999), 7, '0', STR_PAD_LEFT),
                                'parent_id'                   => $parent->id
                            ]);
                        }
                    }
                }
            }
        }
    }
}
