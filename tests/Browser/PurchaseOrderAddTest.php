<?php

namespace Tests\Browser;

use App\Models\Supplier;
use App\Models\Branch;
use App\Constants\BranchColumns;
use App\Constants\SupplierColumns;
use Illuminate\Support\Facades\DB;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Carbon\Carbon;

class PurchaseOrderAddTest extends DuskTestCase
{
    protected $supplier;
    protected $branch;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Run migrations
        $this->artisan('migrate:fresh');

        // Create a supplier for testing
        $this->supplier = Supplier::create([
            'supplier_id' => 'SUP001',
            'company_name' => 'PT. Vendor Test',
            'address' => 'Jl. Test No. 42, Jakarta',
            'telephone' => '081234567890',
            'bank_account' => '1234567890 (BCA)',
        ]);

        // Create a branch for testing
        $this->branch = Branch::create([
            BranchColumns::NAME => 'Cabang Jakarta',
            BranchColumns::ADDRESS => 'Jl. Test No. 1, Jakarta',
            BranchColumns::PHONE => '0211234567',
            BranchColumns::IS_ACTIVE => true,
        ]);
    }

    /**
     * Test user can add new purchase order successfully via POST request.
     */
    public function test_user_can_add_new_purchase_order()
    {
        // Prepare item data (array of items)
        $itemsData = [
            [
                'po_number' => 'PO0001',
                'sku' => 'SKU001',
                'qty' => 10,
                'amount' => 500000,
            ],
            [
                'po_number' => 'PO0001',
                'sku' => 'SKU002',
                'qty' => 5,
                'amount' => 250000,
            ],
        ];

        // Prepare header data (last element in array)
        $headerData = [
            'po_number' => 'PO0001',
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->supplier_id,
            'total' => 750000,
            'order_date' => Carbon::now()->format('Y-m-d'),
        ];

        // Combine items with header (header must be last)
        $allData = array_merge($itemsData, [$headerData]);

        // Send POST request
        $response = $this->post('/purchase_orders/add', $allData);

        // Assert redirect back with success message
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Assert database - PO header should exist
        $this->assertDatabaseHas(config('db_constants.table.po'), [
            'po_number' => $headerData['po_number'],
            'supplier_id' => $headerData['supplier_id'],
            'branch_id' => $headerData['branch_id'],
        ]);

        // Assert database - PO details should exist
        foreach ($itemsData as $item) {
            $this->assertDatabaseHas(config('db_constants.table.po_detail'), [
                'po_number' => $item['po_number'],
                'product_id' => $item['sku'],
                'quantity' => $item['qty'],
                'amount' => $item['amount'],
            ]);
        }
    }

    /**
     * Test validation error when supplier_id is empty.
     */
    public function test_validation_error_when_supplier_id_empty()
    {
        $itemsData = [
            [
                'po_number' => 'PO0002',
                'sku' => 'SKU001',
                'qty' => 5,
                'amount' => 250000,
            ],
        ];

        // Header data WITHOUT supplier_id
        $headerData = [
            'po_number' => 'PO0002',
            'branch_id' => $this->branch->id,
            'supplier_id' => '', // Empty supplier_id
            'total' => 250000,
            'order_date' => Carbon::now()->format('Y-m-d'),
        ];

        $allData = array_merge($itemsData, [$headerData]);

        // Send POST request
        $response = $this->post('/purchase_orders/add', $allData);

        // Assert validation error
        $response->assertSessionHasErrors('supplier_id');
    }

    /**
     * Test validation error when quantity is less than 1.
     */
    public function test_validation_error_when_quantity_less_than_one()
    {
        // Item with invalid qty (0)
        $itemsData = [
            [
                'po_number' => 'PO0003',
                'sku' => 'SKU001',
                'qty' => 0, // Invalid - must be min 1
                'amount' => 0,
            ],
        ];

        $headerData = [
            'po_number' => 'PO0003',
            'branch_id' => $this->branch->id,
            'supplier_id' => $this->supplier->supplier_id,
            'total' => 0,
            'order_date' => Carbon::now()->format('Y-m-d'),
        ];

        $allData = array_merge($itemsData, [$headerData]);

        // Send POST request
        $response = $this->post('/purchase_orders/add', $allData);

        // Assert validation error for qty
        $response->assertSessionHasErrors('qty');
    }
}