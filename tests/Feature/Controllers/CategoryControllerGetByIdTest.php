<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use App\Models\Category;
use App\Http\Controllers\CategoryController;

class CategoryControllerGetByIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('migrate');

        // register a temporary route that maps to the controller action for tests
        Route::get('/test/categories/{id}', [CategoryController::class, 'getCategoryById']);
    }

    //  Case 1: existing category -> returns view with category (200)
    public function test_get_category_by_id_returns_view_with_category_when_exists()
    {
        // Arrange
        $category = Category::create([
            'category' => 'Electronics',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        // Act (use registered test route)
        $response = $this->get("/test/categories/{$category->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('category', function ($c) use ($category) {
            return $c->id === $category->id && ($c->category ?? null) === 'Electronics';
        });
    }

    //  Case 2: non-existent id -> returns JSON 404 with message (per controller)
    public function test_get_category_by_id_returns_404_json_when_not_found()
    {
        // Act
        $response = $this->get("/test/categories/999999");

        // Assert
        $response->assertStatus(404);
        $response->assertExactJson(['message' => 'Category not found']);
    }

    //  Case 3: category with children -> returned view's category includes children relationship
    public function test_get_category_by_id_includes_parent_and_children_relationships()
    {
        // Arrange - create parent and two children
        $parent = Category::create([
            'category' => 'Parent Category',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        $child1 = Category::create([
            'category' => 'Child One',
            'parent_id' => $parent->id,
            'is_active' => 1,
        ]);

        $child2 = Category::create([
            'category' => 'Child Two',
            'parent_id' => $parent->id,
            'is_active' => 1,
        ]);

        // Act
        $response = $this->get("/test/categories/{$parent->id}");

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('category', function ($c) use ($parent) {
            return $c->id === $parent->id
                && isset($c->children)
                && is_countable($c->children)
                && count($c->children) === 2;
        });
    }
}