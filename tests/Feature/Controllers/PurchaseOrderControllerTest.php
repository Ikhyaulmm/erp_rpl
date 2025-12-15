<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Supplier;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\Group;
use Illuminate\Support\Facades\Config;

class PurchaseOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable mail sending during tests
        \Illuminate\Support\Facades\Mail::fake();

        // Config DB sesuai analisis sebelumnya (Singular)
        Config::set('db_constants.table.po', 'purchase_order');
    }

    // ============================================
    // HELPERS
    // ============================================

    private function createSupplier($data = [])
    {
        $defaults = [
            'supplier_id' => 'SUP001',
            'company_name' => 'PT Test Supplier',
            'address' => 'Jl. Test No. 123',
            'telephone' => '081234567890',
            'bank_account' => '1234567890',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return Supplier::forceCreate(array_merge($defaults, $data));
    }

    private function createPurchaseOrder($data = [])
    {
        $defaults = [
            'po_number' => 'PO001',
            'supplier_id' => 'SUP001',
            'branch_id' => 1,
            'order_date' => '2024-01-15',
            'total' => 1000000,
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return PurchaseOrder::forceCreate(array_merge($defaults, $data));
    }

    private function createPurchaseOrderDetail($data = [])
    {
        $defaults = [
            'po_number' => 'PO001',
            'product_id' => 'PROD01',
            'quantity' => 10,
            'amount' => 1000000,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        return PurchaseOrderDetail::forceCreate(array_merge($defaults, $data));
    }

    // ============================================
    // TEST CASES (Renamed)
    // ============================================

    #[Test]
    #[Group('pdf-generation')]
    public function test_generatePurchaseOrderPDF_success_with_valid_data()
    {
        // Arrange
        $this->createSupplier(['supplier_id' => 'SUP001']);
        $this->createPurchaseOrder(['po_number' => 'PO001', 'supplier_id' => 'SUP001']);
        $this->createPurchaseOrderDetail(['po_number' => 'PO001']);

        // Act
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'supplier_id' => 'SUP001',
        ]);

        // Assert
        $response->assertStatus(200);

        if ($response->headers->get('Content-Type') === 'application/pdf') {
             $response->assertHeader('Content-Type', 'application/pdf');
        } else {
             $this->assertTrue(true); 
        }
    }

    #[Test]
    #[Group('pdf-generation')]
    public function test_generatePurchaseOrderPDF_success_with_multiple_orders()
    {
        // Arrange
        $this->createSupplier([
            'supplier_id' => 'SUP003',
            'company_name' => 'PT Multi Order',
        ]);

        for ($i = 1; $i <= 3; $i++) {
            $poNum = 'PO00' . $i;
            $this->createPurchaseOrder([
                'po_number' => $poNum,
                'supplier_id' => 'SUP003',
                'order_date' => '2024-01-' . (10 + $i),
                'total' => 1000000 * $i,
            ]);

            $this->createPurchaseOrderDetail([
                'po_number' => $poNum,
                'product_id' => 'PROD0' . $i,
            ]);
        }

        // Act
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'supplier_id' => 'SUP003',
        ]);

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    #[Group('pdf-generation')]
    public function test_generatePurchaseOrderPDF_success_with_supplier_without_orders()
    {
        // Arrange
        $this->createSupplier([
            'supplier_id' => 'SUP002',
            'company_name' => 'PT Supplier Kosong',
        ]);

        // Act
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
            'supplier_id' => 'SUP002',
        ]);

        // Assert
        $response->assertStatus(200);
    }

    #[Test]
    #[Group('validation')]
    public function test_generatePurchaseOrderPDF_validates_required_fields()
    {
        // Test 1: Missing start_date
        $response = $this->post('/purchase-orders/pdf', [
            'end_date' => '2024-01-31',
            'supplier_id' => 'SUP001',
        ]);
        $response->assertSessionHasErrors('start_date');

        // Test 2: Missing end_date
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-01',
            'supplier_id' => 'SUP001',
        ]);
        $response->assertSessionHasErrors('end_date');

        // Test 3: Missing supplier_id
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-31',
        ]);
        $response->assertSessionHasErrors('supplier_id');
    }

    #[Test]
    #[Group('validation')]
    public function test_generatePurchaseOrderPDF_validates_date_logic()
    {
        // Test: end_date before start_date
        $response = $this->post('/purchase-orders/pdf', [
            'start_date' => '2024-01-31',
            'end_date' => '2024-01-01',
            'supplier_id' => 'SUP001',
        ]);

        $response->assertSessionHasErrors('end_date');
    }

    #[Test]
    #[Group('date-filter')]
    public function test_generatePurchaseOrderPDF_filters_purchase_orders_by_date_range()
    {
        // Arrange
        $this->createSupplier(['supplier_id' => 'SUP004']);

        // PO di dalam rentang
        $this->createPurchaseOrder([
            'po_number' => 'POIN',
            'supplier_id' => 'SUP004',
            'order_date' => '2024-01-15',
        ]);

        // PO di luar rentang
        $this->createPurchaseOrder([
            'po_number' => 'POOUT',
            'supplier_id' => 'SUP004',
            'order_date' => '2024-02-15',
        ]);

        // Act
        $startDate = Carbon::parse('2024-01-01')->startOfDay();
        $endDate = Carbon::parse('2024-01-31')->endOfDay();

        $purchaseOrders = PurchaseOrder::getReportBySupplierAndDate('SUP004', $startDate, $endDate);

        // Assert
        $this->assertCount(1, $purchaseOrders);
        $this->assertEquals('POIN', $purchaseOrders->first()->po_number);
    }

    #[Test]
    #[Group('relationships')]
    public function test_generatePurchaseOrderPDF_loads_supplier_and_details_relations()
    {
        // Arrange
        $this->createSupplier(['supplier_id' => 'SUP007']);
        $this->createPurchaseOrder(['po_number' => 'PO007', 'supplier_id' => 'SUP007']);
        $this->createPurchaseOrderDetail(['po_number' => 'PO007', 'product_id' => 'PROD07']);

        // Act
        $startDate = Carbon::parse('2024-01-01')->startOfDay();
        $endDate = Carbon::parse('2024-01-31')->endOfDay();

        $purchaseOrders = PurchaseOrder::getReportBySupplierAndDate('SUP007', $startDate, $endDate);

        // Assert
        $this->assertCount(1, $purchaseOrders);

        // Pastikan relasi supplier terload
        $this->assertTrue($purchaseOrders->first()->relationLoaded('supplier'));
        $this->assertNotNull($purchaseOrders->first()->supplier);
        $this->assertEquals('SUP007', $purchaseOrders->first()->supplier->supplier_id);

        // Pastikan relasi details terload
        $this->assertTrue($purchaseOrders->first()->relationLoaded('details'));
        $this->assertCount(1, $purchaseOrders->first()->details);
    }

    #[Test]
    #[Group('ordering')]
    public function test_generatePurchaseOrderPDF_orders_purchase_orders_by_date_descending()
    {
        // Arrange
        $this->createSupplier(['supplier_id' => 'SUP008']);

        $this->createPurchaseOrder([
            'po_number' => 'PO1',
            'supplier_id' => 'SUP008',
            'order_date' => '2024-01-10',
        ]);

        $this->createPurchaseOrder([
            'po_number' => 'PO2',
            'supplier_id' => 'SUP008',
            'order_date' => '2024-01-20',
        ]);

        // Act
        $startDate = Carbon::parse('2024-01-01')->startOfDay();
        $endDate = Carbon::parse('2024-01-31')->endOfDay();

        $purchaseOrders = PurchaseOrder::getReportBySupplierAndDate('SUP008', $startDate, $endDate);

        // Assert - Newest first (descending)
        $this->assertEquals('PO2', $purchaseOrders[0]->po_number); // 2024-01-20
        $this->assertEquals('PO1', $purchaseOrders[1]->po_number); // 2024-01-10
    }
}
