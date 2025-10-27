<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ItemFactory extends Factory
{
    public function definition()
    {
        return [
            'product_id'    => 'PROD',
            'sku'           => 'SKU-' . $this->faker->unique()->numberBetween(1, 999),
            'name'          => $this->faker->words(2, true),
            'measurement'   => 'm',
            'base_price'    => 10000,
            'selling_price' => 15000,
            'purchase_unit' => 30,
            'sell_unit'     => 30,
            'stock_unit'    => 100,
        ];
    }
}
