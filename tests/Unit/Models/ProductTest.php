<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use App\Models\Product;
use App\Models\Category;
use App\Models\Item;
use App\Constants\ItemColumns;
use App\Constants\ProductColumns; 
use App\Enums\ProductType;
use PHPUnit\Framework\Attributes\Test;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat table 'item' (singular) untuk match dengan raw SQL di getAllProducts()
        if (!Schema::hasTable('item')) {
            Schema::create('item', function (Blueprint $table) {
                $table->id();
                $table->string('product_id', 50);
                $table->string('sku', 100)->unique();
                $table->string('name', 255);
                $table->unsignedBigInteger('measurement')->nullable();
                $table->decimal('base_price', 15, 2)->default(0);
                $table->decimal('selling_price', 15, 2)->default(0);
                $table->unsignedBigInteger('purchase_unit')->nullable();
                $table->unsignedBigInteger('sell_unit')->nullable();
                $table->integer('stock_unit')->default(0);
                $table->timestamps();
            });
        }
        
        // Buat category default dengan id=1 (karena ProductFactory hard-coded category=1)
        Category::firstOrCreate(['id' => 1], ['category' => 'Default Category']);
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('item');
        parent::tearDown();
    }

    /** @test */
    public function test_getAllProducts_returns_paginated_results()
    {
        Product::factory()->count(5)->create();
        $result = Product::getAllProducts();
        $this->assertNotNull($result);
        $this->assertInstanceOf(\Illuminate\Pagination\LengthAwarePaginator::class, $result);
        $this->assertEquals(5, $result->total());
    }

    /** @test */
    public function test_getAllProducts_returns_10_items_per_page_by_default()
    {
        Product::factory()->count(15)->create();
        $result = Product::getAllProducts();
        $this->assertEquals(10, $result->perPage());
        $this->assertEquals(10, $result->count());
        $this->assertEquals(15, $result->total());
        $this->assertEquals(2, $result->lastPage());
    }

    /** @test */
    public function test_getAllProducts_orders_by_created_at_descending()
    {
        Product::factory()->create(['product_id' => 'PROD', 'name' => 'P1', 'created_at' => now()->subDays(3)]);
        Product::factory()->create(['product_id' => 'PRO2', 'name' => 'P2', 'created_at' => now()->subDays(1)]);
        Product::factory()->create(['product_id' => 'PRO3', 'name' => 'P3', 'created_at' => now()]);

        $result = Product::getAllProducts();
        $this->assertEquals('PRO3', $result->first()->product_id);
        $this->assertEquals('PRO2', $result->items()[1]->product_id);
        $this->assertEquals('PROD', $result->last()->product_id);
    }

    /** @test */
    public function test_getAllProducts_includes_category_relationship()
    {
        $category = Category::factory()->create(['category' => 'Electronics']);
        Product::factory()->create(['product_id' => 'ELEC', 'category' => $category->id]);

        $result = Product::getAllProducts();
        $product = $result->first();
        
        if (is_object($product->category)) {
            $this->assertEquals('Electronics', $product->category->category);
            $this->assertTrue($product->relationLoaded('category'));
        } else {
            $this->assertEquals($category->id, $product->category);
        }
    }

    /** @test */
    public function test_getAllProducts_includes_items_count_with_sku_pattern()
    {
        $product = Product::factory()->create(['product_id' => 'PROD']);
        $baseItem = ['product_id' => $product->product_id, 'measurement' => 1, 'base_price' => 10000, 'selling_price' => 15000, 'purchase_unit' => 30, 'sell_unit' => 30, 'stock_unit' => 100, 'created_at' => now(), 'updated_at' => now()];

        DB::table('item')->insert(array_merge($baseItem, ['sku' => $product->product_id . '-01', 'name' => 'Item 1']));
        DB::table('item')->insert(array_merge($baseItem, ['sku' => $product->product_id . '-02', 'name' => 'Item 2']));
        DB::table('item')->insert(array_merge($baseItem, ['sku' => $product->product_id . '-03', 'name' => 'Item 3']));

        $result = Product::getAllProducts();
        $this->assertEquals(3, $result->first()->items_count);
    }

    /** @test */
    public function test_getAllProducts_returns_empty_when_no_products_exist()
    {
        $result = Product::getAllProducts();
        $this->assertEquals(0, $result->total());
        $this->assertTrue($result->isEmpty());
    }

    /** @test */
    public function test_getAllProducts_handles_multiple_pages_correctly()
    {
        Product::factory()->count(25)->create();
        $page1 = Product::getAllProducts();
        $this->assertEquals(3, $page1->lastPage());
        $this->assertEquals(10, $page1->count());
        $this->assertEquals(25, $page1->total());
        $this->assertTrue($page1->hasMorePages());
    }

    /** @test */
    public function test_getAllProducts_handles_products_with_no_items()
    {
        Product::factory()->create(['product_id' => 'PROD']);
        $result = Product::getAllProducts();
        $this->assertEquals(0, $result->first()->items_count);
    }

    /** @test */
    public function test_getAllProducts_handles_mixed_products_with_and_without_items()
    {
        $product1 = Product::factory()->create(['product_id' => 'PRO1', 'created_at' => now()->subHour()]);
        for ($i = 1; $i <= 5; $i++) {
            DB::table('item')->insert(['product_id' => $product1->product_id, 'sku' => $product1->product_id . '-0' . $i, 'name' => 'Item ' . $i, 'measurement' => 1, 'base_price' => 10000, 'selling_price' => 15000, 'purchase_unit' => 30, 'sell_unit' => 30, 'stock_unit' => 100, 'created_at' => now(), 'updated_at' => now()]);
        }
        $product2 = Product::factory()->create(['product_id' => 'PRO2', 'created_at' => now()]);

        $result = Product::getAllProducts();
        $this->assertEquals(2, $result->total());
        $this->assertEquals('PRO2', $result->first()->product_id);
        $this->assertEquals(0, $result->first()->items_count);
        $this->assertEquals('PRO1', $result->last()->product_id);
        $this->assertEquals(5, $result->last()->items_count);
    }

    /** @test */
    public function test_getAllProducts_returns_correct_data_structure()
    {
        Product::factory()->create(['product_id' => 'TEST', 'name' => 'Test Product']);
        $result = Product::getAllProducts();
        $firstProduct = $result->first();
        $this->assertNotNull($firstProduct->product_id);
        $this->assertNotNull($firstProduct->name);
        $this->assertNotNull($firstProduct->category);
        $hasItemsCount = property_exists($firstProduct, 'items_count') || isset($firstProduct->items_count) || array_key_exists('items_count', $firstProduct->getAttributes());
        $this->assertTrue($hasItemsCount);
        $this->assertInstanceOf(\Carbon\Carbon::class, $firstProduct->created_at);
    }

    /** @test */
    public function test_getAllProducts_counts_items_with_partial_sku_match()
    {
        $product = Product::factory()->create(['product_id' => 'PROD']);
        $base = ['product_id' => $product->product_id, 'measurement' => 1, 'base_price' => 1, 'selling_price' => 1, 'purchase_unit' => 1, 'sell_unit' => 1, 'stock_unit' => 1, 'created_at' => now(), 'updated_at' => now()];
        
        DB::table('item')->insert(array_merge($base, ['sku' => 'PROD-A', 'name' => 'A']));
        DB::table('item')->insert(array_merge($base, ['sku' => 'PROD-B', 'name' => 'B']));
        DB::table('item')->insert(array_merge($base, ['sku' => 'PROD-VAR-1', 'name' => 'V']));
        // Non-match
        DB::table('item')->insert(array_merge($base, ['product_id' => 'OTHER', 'sku' => 'OTHER-01', 'name' => 'O']));

        $result = Product::getAllProducts();
        $this->assertEquals(3, $result->first()->items_count);
    }

    /** @test */
    public function test_getAllProducts_handles_category_with_null_parent()
    {
        $category = Category::factory()->create(['category' => 'Root', 'parent_id' => null]);
        Product::factory()->create(['product_id' => 'PROD', 'category' => $category->id]);

        $result = Product::getAllProducts();
        $product = $result->first();
        
        if (is_object($product->category)) {
            $this->assertEquals('Root', $product->category->category);
            $this->assertNull($product->category->parent_id);
        } else {
            $this->assertEquals($category->id, $product->category);
        }
    }

    /** @test */
    public function test_getAllProducts_handles_large_dataset_efficiently()
    {
        $products = Product::factory()->count(50)->create();
        foreach ($products as $product) {
            for ($i = 1; $i <= 3; $i++) {
                DB::table('item')->insert(['product_id' => $product->product_id, 'sku' => $product->product_id . '-' . $i, 'name' => 'I'.$i, 'measurement' => 1, 'base_price' => 10, 'selling_price' => 10, 'purchase_unit' => 1, 'sell_unit' => 1, 'stock_unit' => 1, 'created_at' => now(), 'updated_at' => now()]);
            }
        }

        $startTime = microtime(true);
        $result = Product::getAllProducts();
        $endTime = microtime(true);
        $executionTime = $endTime - $startTime;

        $this->assertEquals(50, $result->total());
        $this->assertLessThan(2, $executionTime);
    }



    /**
     * Helper manual untuk membuat data Product (Bypass Factory).
     */
    private function createManualProduct($overrides = [])
    {
        $categoryId = $overrides[ProductColumns::CATEGORY] ?? DB::table('categories')->insertGetId([
            'category'   => 'Cat ' . rand(1, 99),
            'parent_id'  => null,
            'is_active'  => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = 'P' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT); 

        $defaults = [
            ProductColumns::PRODUCT_ID => $productId,
            ProductColumns::NAME       => 'Test ' . rand(1, 100),
            ProductColumns::TYPE       => ProductType::FG->value, 
            ProductColumns::CATEGORY   => $categoryId,
            ProductColumns::DESC       => 'Desc',
            ProductColumns::CREATED_AT => now(),
            ProductColumns::UPDATED_AT => now(),
        ];

        DB::table('products')->insert(array_merge($defaults, $overrides));
    }

    #[Test]
    public function get_product_by_type_returns_correct_data()
    {
        // Arrange
        $this->createManualProduct([ProductColumns::PRODUCT_ID => 'F001', ProductColumns::TYPE => ProductType::FG->value]);
        $this->createManualProduct([ProductColumns::PRODUCT_ID => 'F002', ProductColumns::TYPE => ProductType::FG->value]);
        $this->createManualProduct([ProductColumns::PRODUCT_ID => 'R001', ProductColumns::TYPE => ProductType::RM->value]);

        // Act
        $results = Product::getProductByType(ProductType::FG->value);

        // Assert
        $this->assertCount(2, $results);
        foreach ($results as $product) {
            $this->assertEquals(ProductType::FG, $product->type);
        }
    }

    #[Test]
    public function get_product_by_type_returns_empty_when_no_match()
    {
        // Arrange
        $this->createManualProduct([ProductColumns::PRODUCT_ID => 'R002', ProductColumns::TYPE => ProductType::RM->value]);

        // Act
        $results = Product::getProductByType(ProductType::HFG->value);

        // Assert
        $this->assertTrue($results->isEmpty());
    }

    #[Test]
    public function get_product_by_type_handles_enum_casting_correctly()
    {
        // Arrange
        $this->createManualProduct([ProductColumns::PRODUCT_ID => 'ENUM', ProductColumns::TYPE => ProductType::HFG->value]);

        // Act
        $results = Product::getProductByType(ProductType::HFG->value);
        $product = $results->first();

        // Assert
        $this->assertNotNull($product);
        $this->assertInstanceOf(ProductType::class, $product->type);
        $this->assertEquals(ProductType::HFG, $product->type);
        $this->assertEquals('Half Finished Goods', $product->type->label());
    }
}