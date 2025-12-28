<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Category;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;

class CategoryControllerTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // --- SETUP DATABASE DUMMY (SAMA SEPERTI SEBELUMNYA) ---
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        $tableName = config('db_tables.category', 'category');
        if (!Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) {
                $table->id();
                $table->string('category', 50);
                $table->integer('parent_id')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Category::truncate(); 
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // --- DATA DUMMY ---
        Category::create(['category' => 'Elektronik', 'is_active' => 1]);
        Category::create(['category' => 'Furniture', 'is_active' => 1]);
        Category::create(['category' => 'Makanan', 'is_active' => 1]);
    }

    public function test_search_category_with_valid_keyword_returns_results()
    {
        // PERBAIKAN 1: Ganti '?q=' menjadi '?search=' sesuai controller
        $response = $this->get('/categories/search?search=Elektronik');

        // Assert
        $response->assertStatus(200);
        
        // PERBAIKAN 2: Ganti 'category.list' menjadi 'category.index'
        $response->assertViewIs('category.index'); 
        
        // PERBAIKAN 3: Ganti 'category' menjadi 'categories' (jamak)
        $categories = $response->viewData('categories');
        
        $this->assertNotEmpty($categories);
        // Cek apakah data mengandung Elektronik
        $this->assertTrue($categories->contains('category', 'Elektronik'));
        // Cek apakah data TIDAK mengandung Furniture
        $this->assertFalse($categories->contains('category', 'Furniture'));
    }

    public function test_search_category_with_partial_keyword()
    {
        // Ganti '?q=' jadi '?search='
        $response = $this->get('/categories/search?search=Elek');

        $response->assertStatus(200);
        // Ganti 'category' jadi 'categories'
        $categories = $response->viewData('categories');
        
        $this->assertTrue($categories->contains('category', 'Elektronik'));
    }

    public function test_search_category_returns_empty_when_no_match()
    {
        // Ganti '?q=' jadi '?search='
        $response = $this->get('/categories/search?search=Otomotif');

        $response->assertStatus(200);
        // Ganti 'category' jadi 'categories'
        $categories = $response->viewData('categories');
        
        $this->assertEmpty($categories);
    }

    public function test_search_category_without_keyword_returns_all()
    {
        // Tanpa keyword
        $response = $this->get('/categories/search');

        $response->assertStatus(200);
        // Ganti 'category' jadi 'categories'
        $categories = $response->viewData('categories');
        
        // Harusnya ada 3 data (Elektronik, Furniture, Makanan)
        $this->assertCount(3, $categories);
    }
}