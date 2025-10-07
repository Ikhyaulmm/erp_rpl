<?php

namespace Database\Factories;

use App\Models\PurchaseOrderDetail;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderDetailFactory extends Factory
{
    protected $model = PurchaseOrderDetail::class;

    public function definition()
    {
        return [
            'po_number' => $this->faker->regexify('PO[0-9]{6}'),
            'product_id' => function () {
                // Use existing item SKU or create one
                return Item::inRandomOrder()->value('sku') ?? Item::factory()->create()->sku;
            },
            'quantity' => $this->faker->numberBetween(1, 100),
            'amount' => $this->faker->randomFloat(2, 10000, 1000000),
        ];
    }

    /**
     * Create purchase order detail for specific product_id (SKU)
     */
    public function forProductId($productId)
    {
        return $this->state(function (array $attributes) use ($productId) {
            return [
                'product_id' => $productId,
            ];
        });
    }
}