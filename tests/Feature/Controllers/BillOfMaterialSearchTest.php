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

        // 1. Ambil nama tabel langsung dari Model
        // Ini memastikan tabel yang kita buat SAMA PERSIS dengan yang dicari Model
        $tableName = (new BillOfMaterial())->getTable();

        // 2. Bersihkan & Buat Tabel menggunakan nama dinamis tersebut
        Schema::dropIfExists($tableName);
        Schema::dropIfExists('users');

        Schema::create($tableName, function (Blueprint $table) {
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
        
        // Ambil nama tabel dari Model agar insert ke tempat yang benar
        $tableName = (new BillOfMaterial())->getTable();

        DB::table($tableName)->insert([
            ['bom_id' => 'BOM001', 'bom_name' => 'Laptop Gaming', 'created_at' => now(), 'updated_at' => now()],
            ['bom_id' => 'BOM002', 'bom_name' => 'Meja Kantor', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($user)
                         ->get('/bill-of-material/search/Laptop');

        $response->assertStatus(200);
        
        // Debugging: Jika masih gagal, uncomment baris ini untuk melihat responnya
        // dump($response->json());

        $response->assertSee('Laptop Gaming');
        $response->assertDontSee('Meja Kantor');
    }

    /** @test */
    public function it_returns_empty_when_keyword_not_found()
    {
        $user = User::factory()->create();
        $tableName = (new BillOfMaterial())->getTable();

        DB::table($tableName)->insert([
            ['bom_id' => 'BOM001', 'bom_name' => 'Laptop Gaming', 'created_at' => now(), 'updated_at' => now()],
        ]);

        $response = $this->actingAs($user)
                         ->get('/bill-of-material/search/Mobil');

        $response->assertStatus(200);
        $response->assertDontSee('Laptop Gaming');
    }
}