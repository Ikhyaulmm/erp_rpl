<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class getCategoryByIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // run migrations for test DB
        $this->artisan('migrate');
    }

    // Test Case 1: Get category by valid ID returns category with parent info
    public function test_getCategoryById_with_valid_id_returns_category()
    {
        // Arrange: Create parent category
        $parent = Category::create([
            'category' => 'Electronics',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        // Create child category
        $category = Category::create([
            'category' => 'Smartphones',
            'parent_id' => $parent->id,
            'is_active' => 1,
        ]);

        // Act
        $result = Category::getCategoryById($category->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Smartphones', $result->category);
        $this->assertEquals('Electronics', $result->parent_id);
    }

    // Test Case 2: Get category by invalid ID returns null
    public function test_getCategoryById_with_invalid_id_returns_null()
    {
        // Arrange: No categories created

        // Act
        $result = Category::getCategoryById(999);

        // Assert
        $this->assertNull($result);
    }

    // Test Case 3: Get category without parent returns category with 'Tanpa Induk'
    public function test_getCategoryById_without_parent_returns_category_with_tanpa_induk()
    {
        // Arrange: Create category without parent
        $category = Category::create([
            'category' => 'Books',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        // Act
        $result = Category::getCategoryById($category->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Books', $result->category);
        $this->assertEquals('Tanpa Induk', $result->parent_id);
    }
}