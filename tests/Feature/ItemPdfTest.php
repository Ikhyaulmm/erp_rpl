<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Schema; // Tambahan Import
use Illuminate\Database\Schema\Blueprint; // Tambahan Import
use Tests\TestCase;
use App\Models\User;
use App\Models\Item;

class ItemPdfTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Setup: Buat tabel database manual sebelum test berjalan
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat tabel 'users' agar User::factory() tidak error
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // 2. Buat tabel 'item' agar Item::factory() tidak error
        // (Struktur diambil dari ProductTest.php Anda sebelumnya)
        Schema::create('item', function (Blueprint $table) {
            $table->id();
            $table->string('product_id', 50)->nullable();
            $table->string('sku', 100)->nullable(); // Ubah ke nullable jaga-jaga factory kosong
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

    /**
     * Test apakah user bisa mendownload laporan PDF item.
     * @test
     */
    public function user_can_download_items_pdf_report(): void
    {
        // 1. ARRANGE
        $user = User::factory()->create();
        Item::factory()->count(5)->create();

        // 2. ACT
        $response = $this->actingAs($user)
                         ->get('/item/pdf-report');

        // 3. ASSERT
        // Note: Ini akan tetap gagal (404/500) sampai Anda membuat Route & Controllernya
        $response->assertStatus(200);
        $response->assertDownload('laporan_item.pdf');
    }
}