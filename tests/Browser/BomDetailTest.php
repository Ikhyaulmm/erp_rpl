<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Item;
use App\Models\Category;
use App\Models\BillOfMaterial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;

class BomDetailTest extends DuskTestCase
{
    protected $user;
    protected $bomId;

    /**
     * Setup data awal sebelum setiap test berjalan.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Config Table Names sesuai context project
        Config::set('db_tables.item', 'items');
        Config::set('db_constants.table.bom', 'bill_of_material');
        Config::set('db_constants.table.bom_detail', 'bom_detail');
        Config::set('db_constants.table.products', 'products');
        Config::set('db_table.category', 'categories');
        Config::set('db_constants.table.mu', 'measurement_units');

        // 2. Reset Database
        $this->artisan('migrate:fresh');

        // 3. Buat Tabel 'users' Manual jika belum ada (mengikuti pola SupplierPicTest)
        if (!Schema::hasTable('users')) {
            DB::statement('
                CREATE TABLE users (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    email_verified_at TIMESTAMP NULL,
                    password VARCHAR(255) NOT NULL,
                    remember_token VARCHAR(100) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL
                )
            ');
        }

        // 4. Buat User Admin untuk Login
        $this->user = User::create([
            'name' => 'Admin Test',
            'email' => 'admin@erp.com',
            'password' => bcrypt('password'),
        ]);

        // 5. Setup Data Dummy (Product -> Item -> BOM -> BOM Detail)
        
        // Create Category (Required for Product)
        $category = Category::create([
            'category' => 'Bahan Baku',
            'parent_id' => null,
            'is_active' => 1,
        ]);

        // Create Product (Parent of Item) - Use Model to handle column mapping
        Product::create([
            'product_id' => 'P001', // Diubah menjadi 4 karakter sesuai batasan DB/Validasi
            'name' => 'Tepung Terigu',
            'type' => 'RM',
            'category' => $category->id,
            'description' => 'Bahan baku utama',
        ]);

        // Create Measurement Unit (Required for Item)
        if (!Schema::hasTable('measurement_units')) {
            DB::statement('CREATE TABLE measurement_units (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                unit_name VARCHAR(50),
                abbreviation VARCHAR(10),
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )');
        }

        $unitId = DB::table('measurement_units')->insertGetId([
            'unit_name' => 'Kilogram',
            'abbreviation' => 'KG',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pcsUnitId = DB::table('measurement_units')->insertGetId([
            'unit_name' => 'Pieces',
            'abbreviation' => 'PCS',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Item (Referenced by BOM Detail SKU)
        Item::create([
            'product_id' => 'P001', // Sesuaikan dengan product_id di atas
            'sku' => 'ITEM-001',
            'name' => 'Tepung Segitiga Biru', // Model uses 'name'
            'measurement' => $unitId, // Model uses 'measurement' (Integer ID)
            'avg_base_price' => 10000,
            'selling_price' => 12000,
            'purchase_unit' => $unitId, // Integer ID
            'sell_unit' => $unitId,     // Integer ID
            'stock_unit' => $unitId,    // Integer ID
        ]);

        // Create Bill of Material (Header)
        $bom = BillOfMaterial::create([
            'bom_id' => 'BOM-001',
            'bom_name' => 'Resep Roti Tawar',
            'measurement_unit' => $pcsUnitId,
            'total_cost' => 50000,
            'active' => 1,
        ]);
        $this->bomId = $bom->id;

        // Create BOM Detail (Components)
        DB::table('bom_detail')->insert([
            'bom_id' => 'BOM-001', // Menggunakan string ID sesuai logika controller
            'sku' => 'ITEM-001',
            'quantity' => 5,
            'cost' => 10000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Skenario: User login, buka list material (BOM), dan melihat detail BOM.
     */
   public function test_user_can_view_bom_detail()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    // 1. Kunjungi halaman list BOM yang benar, yaitu /bom/list
                    ->visit('/bom/list')
                    ->assertSee('Daftar Bill of Materials') // Verifikasi bahwa kita ada di halaman yang benar

                    // 2. Klik tombol "Lihat" yang sesuai.
                    // Berdasarkan analisis file blade, tombolnya adalah <button> bukan <a>
                    // dan tidak memiliki href. Kita akan klik tombol di baris pertama
                    // karena data di view adalah statis.
                    ->click('table.table-bordered tbody tr:first-child .btn-info')

                    // 3. Tunggu modal detail muncul dan validasi isinya.
                    // Detail ditampilkan di dalam modal, bukan halaman baru.
                    ->whenAvailable('.modal#bomModal', function ($modal) {
                        $modal->assertSee('Detail Bill of Material')
                              // Data di dalam modal di-fetch oleh JavaScript,
                              // jadi kita bisa memvalidasi data dinamis yang kita buat di setup.
                              ->waitForText('Resep Roti Tawar') // Tunggu teks dari AJAX call
                              ->assertSee('Resep Roti Tawar')   // Nama BOM dari data setup
                              ->assertSee('ITEM-001')           // SKU dari data setup
                              ->assertSee('5');                // Quantity dari data setup
                    });
        });
    }
}