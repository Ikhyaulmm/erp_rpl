<?php

namespace Tests\Unit\Models;

use Tests\TestCase; // Gunakan Tests\TestCase agar bisa akses Helper Laravel
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseOrderModel; // Pastikan nama Model sesuai

class PurchaseOrderByIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Antisipasi jika Model menggunakan Config untuk nama tabel
        Config::set('db_tables.purchase_orders', 'purchase_orders');

        // 2. Bersihkan tabel lama jika ada
        Schema::dropIfExists('purchase_orders');

        // 3. Buat Tabel Dummy 'purchase_orders' untuk testing
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique(); // Key yang akan dites
            $table->string('supplier_id')->nullable();
            $table->string('status')->default('pending');
            $table->date('order_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Skenario 1: Berhasil mendapatkan data berdasarkan PO Number
     * @test
     */
    public function it_can_get_purchase_order_by_id()
    {
        // ARRANGE: Buat data dummy
        DB::table('purchase_orders')->insert([
            'po_number' => 'PO-2026-001',
            'supplier_id' => 'SUP001',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ACT: Panggil fungsi yang akan dites
        // Pastikan method ini Static atau adjust sesuai Model Anda
        $result = PurchaseOrderModel::getPurchaseOrderByID('PO-2026-001');

        // ASSERT: Cek hasilnya
        $this->assertNotNull($result, 'Hasil tidak boleh null jika data ada.');
        $this->assertEquals('PO-2026-001', $result->po_number);
        $this->assertEquals('approved', $result->status);
    }

    /**
     * Skenario 2: Return Null jika PO Number tidak ditemukan
     * @test
     */
    public function it_returns_null_if_po_number_not_found()
    {
        // ACT: Panggil fungsi dengan ID ngawur
        $result = PurchaseOrderModel::getPurchaseOrderByID('PO-GAIB-999');

        // ASSERT: Harusnya null
        $this->assertNull($result, 'Hasil harus null jika PO Number tidak ditemukan.');
    }
}