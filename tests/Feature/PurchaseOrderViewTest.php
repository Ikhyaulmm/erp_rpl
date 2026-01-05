<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PurchaseOrder;

class PurchaseOrderViewTest extends TestCase
{
    /**
     * Test view menampilkan jumlah purchase order yang benar
     */
    public function test_view_displays_correct_purchase_order_count()
    {
        // Arrange - Hitung total PO dari database
        $expectedCount = PurchaseOrder::count();

        // Act - Request ke halaman list purchase orders
        $response = $this->get(route('purchase.orders'));

        // Assert - Response sukses
        $response->assertStatus(200);
        
        // Assert - View yang benar digunakan
        $response->assertViewIs('purchase_orders.list');
        
        // Assert - Variable totalOrders ada di view
        $response->assertViewHas('totalOrders');
        
        // Assert - Nilai totalOrders sesuai dengan count database
        $response->assertViewHas('totalOrders', $expectedCount);
        
        // Assert - Badge menampilkan angka yang benar di HTML
        $response->assertSee('Purchase Orders');
        $response->assertSee($expectedCount);
        
        // Assert - Badge HTML dengan class yang benar ada
        $response->assertSee('badge bg-primary');
    }

    /**
     * Test view menampilkan 0 ketika tidak ada purchase order
     */
    public function test_view_displays_zero_when_no_purchase_orders()
    {
        // Arrange - Hapus semua PO
        PurchaseOrder::query()->delete();

        // Act
        $response = $this->get(route('purchase.orders'));

        // Assert
        $response->assertStatus(200);
        $response->assertViewHas('totalOrders', 0);
        
        // Badge tetap muncul dengan angka 0
        $response->assertSee('badge bg-primary');
        $response->assertSee('0');
    }

    /**
     * Test model countPurchaseOrder() memberikan hasil yang benar
     */
    public function test_model_count_purchase_order_returns_correct_number()
    {
        // Arrange - Hitung manual dari database
        $expectedCount = PurchaseOrder::query()->count();

        // Act - Panggil method model
        $actualCount = PurchaseOrder::countPurchaseOrder();

        // Assert
        $this->assertEquals($expectedCount, $actualCount);
        $this->assertIsInt($actualCount);
        $this->assertGreaterThanOrEqual(0, $actualCount);
    }
}