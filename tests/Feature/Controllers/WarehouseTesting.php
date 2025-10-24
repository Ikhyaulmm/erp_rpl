<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WarehouseTesting extends TestCase
{
    use RefreshDatabase;

    /**
     * TC-WH-23
     * Test: View warehouse detail with valid ID
     */
    public function test_view_warehouse_detail_with_valid_id()
    {
        // 🧩 ARRANGE
        // Buat data warehouse dummy di database
        $warehouse = Warehouse::factory()->create([
            'warehaouse_name' => 'Main Warehouse',
            'warehaouse_address' => 'Jakarta',
            'capacity' => 1000,
        ]);

        // 🚀 ACT
        // Panggil endpoint controller dengan ID yang valid
        $response = $this->getJson("/api/warehouse/{$warehouse->id}");

        // ✅ ASSERT
        // Pastikan response status sukses dan data sesuai
        $response->assertStatus(200)
                 ->assertJson([
                     'data' => [
                         'id' => $warehouse->id,
                         'warehaouse_name' => 'Main Warehouse',
                         'warehaouse_address' => 'Jakarta',
                         'capacity' => 1000,
                     ],
                 ]);
    }
}
