<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;

class BillOfMaterialSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Config & Schema (Sama seperti sebelumnya)
        Config::set('db_constants.table.bom', 'bill_of_materials');
        Config::set('db_constants.column.bom', []); 

        Schema::dropIfExists('bill_of_materials');
        Schema::dropIfExists('users');

        Schema::create('bill_of_materials', function (Blueprint $table) {
            $table->id();
            $table->string('bom_id')->nullable();
            $table->string('bom_name');
            $table->string('measurement_unit')->nullable();
            $table->string('total_cost')->nullable();
            $table->string('active')->nullable();
            $table->timestamps();
        });

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

    /** @test */
    public function it_can_search_bill_of_material_by_keyword()
    {
        $user = User::factory()->create();

        DB::table('bill_of_materials')->insert([
            ['bom_id' => 'BOM001', 'bom_name' => 'Laptop Gaming', 'created_at' => now(), 'updated_at' => now()],
            ['bom_id' => 'BOM002', 'bom_name' => 'Meja Kantor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // PERBAIKAN: Gunakan format slash (/) bukan tanda tanya (?)
        // Sesuai route: /bill-of-material/search/{keyword}
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

        DB::table('bill_of_materials')->insert([
            ['bom_id' => 'BOM001', 'bom_name' => 'Laptop Gaming', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // PERBAIKAN: Gunakan format slash (/)
        $response = $this->actingAs($user)
                         ->get('/bill-of-material/search/Mobil');

        $response->assertStatus(200);
        $response->assertDontSee('Laptop Gaming');
    }
}