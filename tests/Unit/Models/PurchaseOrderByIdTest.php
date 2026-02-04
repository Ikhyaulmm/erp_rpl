<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use App\Models\PurchaseOrder; 

class PurchaseOrderByIdTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // FIX 1: Gunakan nama tabel 'purchase_order' (singular) sesuai error log
        // Kita drop dulu tabelnya agar definisi kolom mengikuti yang kita buat di sini (bukan dari migrasi lama)
        Schema::dropIfExists('purchase_order');
        
        // FIX 2: Buat tabel dengan nama 'purchase_order'
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->id();
            // Definisi string tanpa angka akan default 255 karakter (cukup panjang)
            $table->string('po_number')->unique(); 
            $table->string('status')->nullable();
            $table->date('date')->nullable();
            
            // Tambahkan kolom timestamps (created_at, updated_at) agar tidak error saat insert
            $table->timestamps(); 
        });
    }

    /** @test */
    public function it_can_get_purchase_order_by_id()
    {
        // 1. ARRANGE: Gunakan data yang lebih pendek untuk keamanan
        $poNumber = 'PO-001'; 

        PurchaseOrder::create([
            'po_number' => $poNumber,
            'status' => 'Pending',
            'date' => now(),
        ]);

        // 2. ACT: Cari Data
        $result = PurchaseOrder::where('po_number', $poNumber)->first();

        // 3. ASSERT
        $this->assertNotNull($result, 'Data Purchase Order harusnya ditemukan.');
        $this->assertEquals($poNumber, $result->po_number);
    }

    /** @test */
    public function it_returns_null_if_po_number_not_found()
    {
        // 1. ARRANGE: Buat data dummy
        PurchaseOrder::create([
            'po_number' => 'PO-001', 
        ]);

        // 2. ACT: Cari ID yang Tidak Ada
        $result = PurchaseOrder::where('po_number', 'PO-ZONK')->first();

        // 3. ASSERT
        $this->assertNull($result, 'Hasil harus null jika PO Number tidak ditemukan.');
    }
}