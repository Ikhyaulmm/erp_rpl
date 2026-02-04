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

        Schema::dropIfExists('purchase_order');
        
        Schema::create('purchase_order', function (Blueprint $table) {
            $table->char('po_number', 6);
            $table->char('supplier_id', 6);
            
            $table->bigInteger('total')->nullable();
            $table->integer('branch_id')->nullable();
            $table->date('order_date')->nullable();
            $table->char('status', 20)->nullable();
            
            $table->timestamps();

            $table->primary(['po_number', 'supplier_id']);
        });
    }

    /** @test */
    public function it_can_get_purchase_order_by_id()
    {
        // 1. ARRANGE
        $poNumber = 'PO001'; 
        $supplierId = 'SUP001';

        PurchaseOrder::create([
            'po_number' => $poNumber,
            'supplier_id' => $supplierId,
            'total' => 150000,
            'branch_id' => 1,
            'status' => 'Pending',
            'order_date' => now(),
        ]);

        // 2. ACT
        $result = PurchaseOrder::where('po_number', $poNumber)->first();

        // 3. ASSERT
        $this->assertNotNull($result, 'Data Purchase Order harusnya ditemukan.');
        $this->assertEquals($poNumber, $result->po_number);
    }

    /** @test */
    public function it_returns_null_if_po_number_not_found()
    {
        // 1. ARRANGE
        PurchaseOrder::create([
            'po_number' => 'PO001', 
            'supplier_id' => 'SUP001',
        ]);

        // 2. ACT
        $result = PurchaseOrder::where('po_number', 'PO999')->first();

        // 3. ASSERT
        $this->assertNull($result, 'Hasil harus null jika PO Number tidak ditemukan.');
    }
}