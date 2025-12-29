<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PurchaseOrder;
// Import tambahan untuk test baru
use App\Models\Supplier;
use App\Models\PurchaseOrderDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class PurchaseOrderTest extends TestCase
{
    // Trait ini penting! Agar setelah test selesai, data dummy dihapus otomatis.
    // Jadi database asli kamu tidak akan kotor.
    use DatabaseTransactions;

    /**
     * @test
     * Menguji fungsi countPurchaseOrder() dengan banyak data.
     */
    public function testCountPurchaseOrderWithMultipleRecords()
    {
        // ARRANGE: Masukkan Data Dummy
        PurchaseOrder::create([
            'po_number' => 'PO001',
            'branch_id' => 1,
            'supplier_id' => 1,
            'order_date' => now(),
            'total' => 1000,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO002',
            'branch_id' => 1,
            'supplier_id' => 2,
            'order_date' => now(),
            'total' => 2000,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO003',
            'branch_id' => 2,
            'supplier_id' => 1,
            'order_date' => now(),
            'total' => 1500,
        ]);

        // ACT & ASSERT
        $count = PurchaseOrder::countPurchaseOrder();
        $this->assertEquals(3, $count);
    }

    /**
     * @test
     * Menguji fungsi countPurchaseOrder() jika tabel kosong.
     */
    public function testCountPurchaseOrderWithZeroRecords()
    {
        // Tidak ada data yang dibuat
        $count = PurchaseOrder::countPurchaseOrder();
        $this->assertEquals(0, $count);
    }

    /**
     * @test
     * Menguji fungsi countPurchaseOrder() dengan satu data saja.
     */
    public function testCountPurchaseOrderWithSingleRecord()
    {
        // ARRANGE
        PurchaseOrder::create([
            'po_number' => 'PO001',
            'branch_id' => 1,
            'supplier_id' => 1,
            'order_date' => now(),
            'total' => 1000,
        ]);

        // ACT & ASSERT
        $count = PurchaseOrder::countPurchaseOrder();
        $this->assertEquals(1, $count);
    }

    // =========================================================================
    //  TEST UNTUK FUNGSI: getReportBySupplierAndDate
    // =========================================================================

    /**
     * @test
     * Skenario Positif: Memastikan data berhasil diambil sesuai filter tanggal & supplier.
     */
    public function test_get_report_returns_correct_data()
    {
        // 1. ARRANGE (Persiapan Data)
        
        // Buat Supplier Dummy
        Supplier::create([
            'supplier_id' => 'SUP001',
            'company_name' => 'PT Test Simple',
            'address' => '-', 'telephone' => '-', 'bank_account' => '-'
        ]);

        // Buat PO yang MASUK dalam filter (Bulan Januari)
        PurchaseOrder::create([
            'po_number'   => 'PO0010', 
            'branch_id'   => 1,
            'supplier_id' => 'SUP001',
            'order_date'  => '2024-01-15', // Tanggal 15 Januari
            'total'       => 1000
        ]);

        // Buat PO yang DILUAR filter (Bulan Maret) - Data pengecoh
        PurchaseOrder::create([
            'po_number'   => 'PO0020', 
            'branch_id'   => 1,
            'supplier_id' => 'SUP001',
            'order_date'  => '2024-03-15', // Tanggal 15 Maret
            'total'       => 2000
        ]);

        // 2. ACT (Jalankan Fungsi)
        // Kita filter dari tanggal 1 Jan s/d 31 Jan
        $result = PurchaseOrder::getReportBySupplierAndDate(
            'SUP001', 
            '2024-01-01', 
            '2024-01-31'
        );

        // 3. ASSERT (Cek Hasil)
        // Harusnya cuma dapat 1 data (yaitu PO0010 yang bulan Januari)
        $this->assertCount(1, $result);
        $this->assertEquals('PO0010', $result->first()->po_number);
    }

    /**
     * @test
     * Skenario Negatif: Memastikan hasil kosong jika tidak ada tanggal yang cocok.
     */
    public function test_get_report_returns_empty_if_no_match()
    {
        // 1. ARRANGE
        Supplier::create([
            'supplier_id' => 'SUP002', 
            'company_name' => 'PT Kosong', 
            'address' => '-', 'telephone' => '-', 'bank_account' => '-'
        ]);

        // Kita buat datanya bulan MEI
        PurchaseOrder::create([
            'po_number'   => 'PO0030', 
            'branch_id'   => 1,
            'supplier_id' => 'SUP002',
            'order_date'  => '2024-05-15',
            'total'       => 1000
        ]);

        // 2. ACT
        // Kita cari data bulan JANUARI
        $result = PurchaseOrder::getReportBySupplierAndDate(
            'SUP002', 
            '2024-01-01', 
            '2024-01-31'
        );

        // 3. ASSERT
        // Harusnya kosong karena datanya bulan Mei, carinya Januari
        $this->assertCount(0, $result);
    }
    // =========================================================================
    //  TEST UNTUK FUNGSI: getPurchaseOrderByKeywords
    // =========================================================================

    /**
     * @test
     * Test 1: getPurchaseOrderByKeywords tanpa keywords (return semua PO)
     * Scenario: User tidak memberikan keyword, harusnya return semua purchase order
     */
    public function test_get_purchase_order_by_keywords_without_keywords()
    {
        // 1. ARRANGE (Persiapan Data)
        $supplier = Supplier::factory()->create();

        // Buat 3 purchase order
        PurchaseOrder::create([
            'po_number' => 'PO0001',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Draft',
            'total' => 1000000,
            'branch_id' => 1,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO0002',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Approved',
            'total' => 2000000,
            'branch_id' => 1,
        ]);

        PurchaseOrder::create([
            'po_number' => 'PO0003',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Received',
            'total' => 3000000,
            'branch_id' => 1,
        ]);

        // 2. ACT (Jalankan Fungsi)
        $result = PurchaseOrder::getPurchaseOrderByKeywords(null);

        // 3. ASSERT (Cek Hasil)
        // Harusnya return minimal 3 purchase order
        $this->assertGreaterThanOrEqual(3, $result->total());
    }

    /**
     * @test
     * Test 2: getPurchaseOrderByKeywords dengan search berdasarkan po_number
     * Scenario: User search dengan po_number, harusnya return PO yang sesuai
     */
    public function test_get_purchase_order_by_keywords_search_by_po_number()
    {
        // 1. ARRANGE
        $supplier = Supplier::factory()->create();

        // Buat purchase order dengan po_number yang spesifik
        PurchaseOrder::create([
            'po_number' => 'PO9999',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Draft',
            'total' => 5000000,
            'branch_id' => 1,
        ]);

        // Buat PO lainnya yang tidak sesuai
        PurchaseOrder::create([
            'po_number' => 'PO0001',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Approved',
            'total' => 1000000,
            'branch_id' => 1,
        ]);

        // 2. ACT
        $result = PurchaseOrder::getPurchaseOrderByKeywords('PO9999');

        // 3. ASSERT
        // Harusnya mengandung PO yang dicari
        $poNumbers = $result->pluck('po_number')->toArray();
        $this->assertContains('PO9999', $poNumbers);
    }

    /**
     * @test
     * Test 3: getPurchaseOrderByKeywords dengan search berdasarkan status
     * Scenario: User search dengan status, harusnya return PO dengan status yang sesuai
     */
    public function test_get_purchase_order_by_keywords_search_by_status()
    {
        // 1. ARRANGE
        $supplier = Supplier::factory()->create();

        // Buat PO dengan status "Pending"
        PurchaseOrder::create([
            'po_number' => 'POT001',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Pending',
            'total' => 1000000,
            'branch_id' => 1,
        ]);

        PurchaseOrder::create([
            'po_number' => 'POT002',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Pending',
            'total' => 2000000,
            'branch_id' => 1,
        ]);

        // Buat PO dengan status "Approved"
        PurchaseOrder::create([
            'po_number' => 'POT003',
            'supplier_id' => $supplier->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Approved',
            'total' => 3000000,
            'branch_id' => 1,
        ]);

        // 2. ACT
        $result = PurchaseOrder::getPurchaseOrderByKeywords('Pending');

        // 3. ASSERT
        $this->assertGreaterThan(0, $result->total());
        
        // Verify ada yang mengandung "Pending"
        $statuses = $result->pluck('status')->toArray();
        $this->assertContains('Pending', $statuses);
    }

    /**
     * @test
     * Test 4: getPurchaseOrderByKeywords dengan search berdasarkan company_name supplier
     * Scenario: User search dengan company_name supplier, harusnya return PO dari supplier tersebut
     */
    public function test_get_purchase_order_by_keywords_search_by_supplier_company_name()
    {
        // 1. ARRANGE
        // Buat supplier dengan company_name yang spesifik
        $supplierTarget = Supplier::factory()->create([
            'company_name' => 'PT Maju Jaya Indonesia',
        ]);

        // Buat supplier lain
        $supplierOther = Supplier::factory()->create([
            'company_name' => 'CV Cinta Sejati',
        ]);

        // Buat PO dari supplier target
        PurchaseOrder::create([
            'po_number' => 'POS001',
            'supplier_id' => $supplierTarget->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Draft',
            'total' => 5000000,
            'branch_id' => 1,
        ]);

        // Buat PO dari supplier lain
        PurchaseOrder::create([
            'po_number' => 'POS002',
            'supplier_id' => $supplierOther->supplier_id,
            'order_date' => now()->toDateString(),
            'status' => 'Approved',
            'total' => 2000000,
            'branch_id' => 1,
        ]);

        // 2. ACT
        // Search dengan company_name (gunakan kata kunci yang ada di nama)
        $result = PurchaseOrder::getPurchaseOrderByKeywords('Maju Jaya');

        // 3. ASSERT
        $this->assertGreaterThan(0, $result->total());
        
        // Verify supplier_id ada di result
        $supplierIds = $result->pluck('supplier_id')->toArray();
        $this->assertContains($supplierTarget->supplier_id, $supplierIds);
    }
}