<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\BillOfMaterial; 

class BillOfMaterialSearchTest extends TestCase
{
    use RefreshDatabase; 

    protected function setUp(): void
    {
        parent::setUp();
        
        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        $tableName = (new BillOfMaterial())->getTable(); 
        Schema::dropIfExists($tableName);
        
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->char('bom_id', 7)->nullable();
            $table->string('bom_name', 100);
            
            $table->tinyInteger('measurement_unit')->nullable(); 
            $table->integer('total_cost')->nullable();
            $table->tinyInteger('active')->nullable();
            
            $table->timestamps();
        });
    }

    /** @test */
    public function it_can_search_bill_of_material_by_keyword()
    {
        $user = User::factory()->create();
        $tableName = (new BillOfMaterial())->getTable();

        DB::table($tableName)->insert([
            [
                'bom_id' => 'BOM001', 
                'bom_name' => 'Laptop Gaming', 
                'measurement_unit' => 1,
                'total_cost' => 10000000,
                'active' => 1, // Angka
                'created_at' => now(), 
                'updated_at' => now()
            ],
            [
                'bom_id' => 'BOM002', 
                'bom_name' => 'Meja Kantor', 
                'measurement_unit' => 1,
                'total_cost' => 500000,
                'active' => 1,
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

        $response = $this->actingAs($user)
                         ->get('/bill-of-material/search/Laptop');

        $response->assertStatus(200);
        $response->assertSee('Laptop Gaming');
        $response->assertDontSee('Meja Kantor');
    }

    /** @test */
    public function it_returns_empty_when_keyword_not_found()
    {
        $user = User::factory()->create();
        $tableName = (new BillOfMaterial())->getTable();

        DB::table($tableName)->insert([
            [
                'bom_id' => 'BOM001', 
                'bom_name' => 'Laptop Gaming', 
                'measurement_unit' => 1,
                'total_cost' => 10000000,
                'active' => 1,
                'created_at' => now(), 
                'updated_at' => now()
            ],
        ]);

        $response = $this->actingAs($user)
                         ->get('/bill-of-material/search/Mobil');

        $response->assertStatus(200);
        $response->assertDontSee('Laptop Gaming');
    }
}