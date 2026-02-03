<?php

namespace Tests\Unit;

use Tests\TestCase;
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

        Config::set('db_tables.purchase_orders', 'purchase_orders');

        Schema::dropIfExists('purchase_orders');

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
        DB::table('purchase_orders')->insert([
            'po_number' => 'PO-2026-001',
            'supplier_id' => 'SUP001',
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = PurchaseOrderModel::getPurchaseOrderByID('PO-2026-001');

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
        $result = PurchaseOrderModel::getPurchaseOrderByID('PO-GAIB-999');

        $this->assertNull($result, 'Hasil harus null jika PO Number tidak ditemukan.');
    }
}