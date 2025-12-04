<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Supplier;
use App\Models\SupplierPic;
use App\Models\PurchaseOrder;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test normal: Count supplier.
     */
    public function testCountSupplier()
    {
        Supplier::create([
            'supplier_id' => 'SUP001',
            'company_name' => 'Test Supplier',
            'address' => 'Test Address',
            'telephone' => '123456789',
            'bank_account' => '1234567890',
        ]);
        Supplier::create([
            'supplier_id' => 'SUP002',
            'company_name' => 'Test Supplier 2',
            'address' => 'Test Address 2',
            'telephone' => '987654321',
            'bank_account' => '0987654321',
        ]);

        $count = Supplier::countSupplier();

        $this->assertEquals(2, $count);
    }

    /**
     * Test count supplier pic.
     */
    public function testCountSupplierPic()
    {
        $supplier = Supplier::factory()->create();
        SupplierPic::create([
            'supplier_id' => $supplier->supplier_id,
            'name' => 'Test PIC 1',
            'email' => 'pic1@test.com',
            'phone_number' => '123456789',
            'assigned_date' => now(),
        ]);
        SupplierPic::create([
            'supplier_id' => $supplier->supplier_id,
            'name' => 'Test PIC 2',
            'email' => 'pic2@test.com',
            'phone_number' => '987654321',
            'assigned_date' => now(),
        ]);
        SupplierPic::create([
            'supplier_id' => $supplier->supplier_id,
            'name' => 'Test PIC 3',
            'email' => 'pic3@test.com',
            'phone_number' => '555666777',
            'assigned_date' => now(),
        ]);

        $count = SupplierPic::countSupplierPIC($supplier->supplier_id);

        $this->assertEquals(3, $count);
    }

    /**
     * Test count PO.
     */
    public function testCountPurchaseOrders()
    {
        PurchaseOrder::create([
            'po_number' => 'PO001',
            'supplier_id' => 'SUP001',
            'branch_id' => 1,
            'order_date' => now(),
            'total' => 1000,
            'status' => 'pending',
        ]);
        PurchaseOrder::create([
            'po_number' => 'PO002',
            'supplier_id' => 'SUP002',
            'branch_id' => 1,
            'order_date' => now(),
            'total' => 2000,
            'status' => 'pending',
        ]);
        PurchaseOrder::create([
            'po_number' => 'PO003',
            'supplier_id' => 'SUP003',
            'branch_id' => 1,
            'order_date' => now(),
            'total' => 3000,
            'status' => 'pending',
        ]);
        PurchaseOrder::create([
            'po_number' => 'PO004',
            'supplier_id' => 'SUP004',
            'branch_id' => 1,
            'order_date' => now(),
            'total' => 4000,
            'status' => 'pending',
        ]);
        PurchaseOrder::create([
            'po_number' => 'PO005',
            'supplier_id' => 'SUP005',
            'branch_id' => 1,
            'order_date' => now(),
            'total' => 5000,
            'status' => 'pending',
        ]);

        $count = PurchaseOrder::countPurchaseOrder();

        $this->assertEquals(5, $count);
    }
}
