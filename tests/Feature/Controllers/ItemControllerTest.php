<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Constants\ItemColumns; // Opsional: Jika ingin pakai constant
use Illuminate\Support\Facades\Schema;

class ItemControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Menjalankan migrasi agar tabel tersedia di database testing
        $this->artisan('migrate');
    }

    /**
     * Helper untuk membuat Item secara manual.
     * Kita buat manual agar tidak error jika Factory Product/MeasurementUnit belum ada.
     */
    private function createItem($overrides = [])
    {
        // Data default sesuai struktur tabel di Migration & Controller
        $data = array_merge([
            'product_id' => 'P001',      // char(4)
            'sku'        => 'SKU-' . rand(1000, 9999),
            'name'       => 'Item Test ' . rand(1, 100), // Kolom di DB: name
            'measurement'=> 'PCS',       // Kolom di DB: measurement
            'base_price' => 10000,
            'selling_price' => 15000,
            'purchase_unit' => 30,
            'sell_unit'     => 30,
            'stock_unit'    => 100,
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides);

        // Insert menggunakan Model Item
        return Item::create($data);
    }

    /**
     * Test 1: getItemAll berhasil mengambil semua data item
     * Route: GET /items
     */
    public function test_getItemAll_returns_all_items_successfully()
    {
        // 1. Arrange: Buat 3 data dummy
        $item1 = $this->createItem(['product_id' => 'A001', 'name' => 'Beras Pandan Wangi']);
        $item2 = $this->createItem(['product_id' => 'B002', 'name' => 'Gula Pasir']);
        $item3 = $this->createItem(['product_id' => 'C003', 'name' => 'Minyak Goreng']);

        // 2. Act: Panggil route GET /items
        $response = $this->get('/items');

        // 3. Assert: Pastikan status 200 OK
        $response->assertStatus(200);

        // 4. Assert: Pastikan format JSON dan jumlah datanya 3
        $response->assertJsonCount(3);

        // 5. Assert: Pastikan data spesifik ada di dalam response
        // Laravel otomatis convert Model ke JSON, jadi key-nya sesuai nama kolom DB
        $response->assertJsonFragment(['product_id' => 'A001']);
        $response->assertJsonFragment(['name' => 'Beras Pandan Wangi']); // DB column 'name'
        $response->assertJsonFragment(['sku' => $item2->sku]);
    }

    /**
     * Test 2: getItemAll mengembalikan array kosong jika database kosong
     */
    public function test_getItemAll_returns_empty_when_no_data()
    {
        // 1. Arrange: Tidak membuat data apa-apa (Database bersih karena RefreshDatabase)

        // 2. Act
        $response = $this->get('/items');

        // 3. Assert
        $response->assertStatus(200);
        
        // Harus array kosong []
        $response->assertJsonCount(0);
        $response->assertExactJson([]);
    }

    /**
     * Test 3: Memastikan struktur JSON sesuai dengan kolom database
     */
    public function test_getItemAll_returns_correct_json_structure()
    {
        // 1. Arrange
        $this->createItem();

        // 2. Act
        $response = $this->get('/items');

        // 3. Assert
        $response->assertStatus(200);
        
        // Cek struktur objek pertama di dalam array
        $response->assertJsonStructure([
            '*' => [ // '*' artinya setiap item dalam array
                'id',
                'product_id',
                'sku',
                'name',
                'measurement',
                'selling_price',
                'created_at',
                'updated_at'
            ]
        ]);
    }
}