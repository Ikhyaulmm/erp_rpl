<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\PurchaseOrder;
// Import tambahan untuk test baru
use App\Models\Supplier;
use App\Models\PurchaseOrderDetail;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use App\Enums\POStatus;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;

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
    //  TEST UNTUK FUNGSI: getReportBySupplierAndDate (DARI UTAMA/DEV)
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

    // =====================================================================
    // KODE BARU (UNTUK FUNGSI countOrdersByDateSupplier)
    // =====================================================================

    /**
     * Helper manual untuk membuat data dummy tanpa Factory.
     * Format PO disesuaikan agar tidak error "Data too long".
     */
    private function createManualPurchaseOrder($overrides = [])
    {
        // Format PO pendek: PO + 3 digit angka (Contoh: PO123)
        $poNumber = 'PO' . rand(100, 999); 

        // Cek jika override punya po_number sendiri
        if (isset($overrides['po_number'])) {
            $poNumber = $overrides['po_number'];
        }

        DB::table('purchase_order')->insert(array_merge([
            'po_number'   => $poNumber,
            'supplier_id' => 'SUP001', // ID Supplier default untuk test ini
            'branch_id'   => 1,
            'order_date'  => '2024-01-15',
            'total'       => 500000,
            'status'      => POStatus::Draft->value,
            'created_at'  => now(),
            'updated_at'  => now(),
        ], $overrides));
    }

    #[Test]
    public function count_orders_logic_is_correct()
    {
        /** * Skenario 1: Logika Dasar & Filter Tanggal/Supplier
         * Memastikan fungsi menghitung data yang valid dan mengabaikan yang tidak valid.
         */
        
        // 1. Data VALID (Target) - Masuk hitungan
        $this->createManualPurchaseOrder(['po_number' => 'PO101', 'supplier_id' => 'SUP-A', 'order_date' => '2024-01-10']); 
        $this->createManualPurchaseOrder(['po_number' => 'PO102', 'supplier_id' => 'SUP-A', 'order_date' => '2024-01-20']);

        // 2. Data INVALID (Tanggal Salah: Februari) - Jangan dihitung
        $this->createManualPurchaseOrder(['po_number' => 'PO103', 'supplier_id' => 'SUP-A', 'order_date' => '2024-02-01']); 

        // 3. Data INVALID (Supplier Salah: SUP-B) - Jangan dihitung
        $this->createManualPurchaseOrder(['po_number' => 'PO104', 'supplier_id' => 'SUP-B', 'order_date' => '2024-01-15']); 

        // ACT
        $result = PurchaseOrder::countOrdersByDateSupplier(
            '2024-01-01', 
            '2024-01-31', 
            'SUP-A'
        );

        // ASSERT
        // Harusnya cuma 2 (Data VALID) yang terhitung
        $this->assertEquals(2, $result, 'Gagal memfilter tanggal atau supplier dengan benar.');
    }

    #[Test]
    public function count_orders_filters_by_status_enum_correctly()
    {
        /**
         * Skenario 2: Filter Status (Enum)
         * Memastikan parameter opsional status berfungsi.
         */

        // Buat 1 yang Approved (Target)
        $this->createManualPurchaseOrder([
            'po_number' => 'PO201',
            'supplier_id' => 'SUP-A', 
            'order_date' => '2024-01-15', 
            'status' => POStatus::Approved->value
        ]);

        // Buat 1 yang Draft (Bukan Target)
        $this->createManualPurchaseOrder([
            'po_number' => 'PO202',
            'supplier_id' => 'SUP-A', 
            'order_date' => '2024-01-15', 
            'status' => POStatus::Draft->value
        ]);

        // ACT (Cari yang Approved saja)
        $result = PurchaseOrder::countOrdersByDateSupplier(
            '2024-01-01', '2024-01-31', 'SUP-A', 
            POStatus::Approved
        );

        // ASSERT
        $this->assertEquals(1, $result, 'Gagal memfilter berdasarkan Status Enum.');
    }

    #[Test]
    public function count_orders_returns_zero_on_empty_data()
    {
        /**
         * Skenario 3: Data Kosong (Sad Path)
         * Memastikan tidak error jika tidak ada data.
         */
        
        $result = PurchaseOrder::countOrdersByDateSupplier(
            '2024-01-01', '2024-01-31', 'SUP-Z'
        );

        $this->assertEquals(0, $result, 'Harusnya return 0 jika data kosong.');
    }
}