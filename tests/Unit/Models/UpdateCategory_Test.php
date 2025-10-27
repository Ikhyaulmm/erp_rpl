<?php

namespace Tests\Feature;
use Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasDynamicColumns;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Category;
use App\Enums\ProductType;
use App\Models\Product;
use Faker\Factory as Faker;

class UpdateCategory_Test extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_modelUpdateCategory(): void
    {
        $faker = Faker::create();
        $category = Category::inRandomOrder()->first();

        $this->assertNotNull($category, 'Category should exist');

        $newData = [
            'category' => $faker->word,
        ];

        $updateCategory = Category::updateCategory($category->id, $newData);

        $this->assertNotNull($updateCategory, 'Updated category should not be null');
        $this->assertEquals($newData['category'], $updateCategory->category);
    }


}
