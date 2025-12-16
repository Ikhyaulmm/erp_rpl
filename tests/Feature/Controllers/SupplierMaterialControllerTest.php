<?php

namespace Tests\Feature\Controllers;

use Tests\TestCase;
use App\Models\SupplierMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SupplierMaterialControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // run migrations for test DB
        $this->artisan('migrate');
    }

    // Test Case 1: Add supplier material with valid data redirects with success
    public function test_addSupplierMaterial_with_valid_data_redirects_with_success()
    {
        // Arrange
        $data = [
            'supplier_id' => 'SUP001',
            'company_name' => 'PT ABC Suppliers',
            'product_id' => 'P001',
            'product_name' => 'Product A',
            'base_price' => 10000,
        ];

        // Act
        $response = $this->post('/supplier/material/add', $data);

        // Assert
        $response->assertRedirect()
                 ->assertSessionHas('success', 'Data supplier product berhasil divalidasi!');
        
        $this->assertDatabaseHas('supplier_product', [
            'supplier_id' => 'SUP001',
            'product_id' => 'P001',
            'base_price' => 10000,
        ]);
    }

    // Test Case 2: Add supplier material with missing required field fails validation
    public function test_addSupplierMaterial_with_missing_supplier_id_fails_validation()
    {
        // Arrange
        $data = [
            'company_name' => 'PT ABC Suppliers',
            'product_id' => 'P001',
            'product_name' => 'Product A',
            'base_price' => 10000,
            // supplier_id missing
        ];

        // Act
        $response = $this->post('/supplier/material/add', $data);

        // Assert
        $response->assertRedirect()
                 ->assertSessionHasErrors('supplier_id');
        
        $this->assertDatabaseMissing('supplier_product', [
            'product_id' => 'P001',
        ]);
    }

    // Test Case 3: Add supplier material with invalid base_price fails validation
    public function test_addSupplierMaterial_with_negative_base_price_fails_validation()
    {
        // Arrange
        $data = [
            'supplier_id' => 'SUP001',
            'company_name' => 'PT ABC Suppliers',
            'product_id' => 'P001',
            'product_name' => 'Product A',
            'base_price' => -5000, // Invalid negative price
        ];

        // Act
        $response = $this->post('/supplier/material/add', $data);

        // Assert
        $response->assertRedirect()
                 ->assertSessionHasErrors('base_price');
        
        $this->assertDatabaseMissing('supplier_product', [
            'supplier_id' => 'SUP001',
            'product_id' => 'P001',
        ]);
    }
}