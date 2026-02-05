<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\Warehouse;

class WarehouseDataTablesTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Test Case 1: Pastikan elemen halaman tampil dengan benar.
     */
    public function test_user_can_access_warehouse_pages()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/warehouses')
                    ->assertSee('List Warehouse')
                    ->assertSee('Tambah')
                    ->assertPresent('table');

            $browser->visit('/warehouses/create')
                    ->assertSee('Tambah Gudang')
                    ->assertPresent('input[name="warehouse_name"]')
                    ->assertButtonEnabled('Simpan');
        });
    }

    /**
     * Test Case 2: Skenario Sukses Membuat Gudang
     */
    public function test_user_can_create_warehouse_success_flow()
    {
        $this->browse(function (Browser $browser) {
            $warehouseName = 'Gudang Testing ' . time();

            $browser->visit('/warehouses/create')
                    ->type('warehouse_name', $warehouseName)
                    ->type('warehouse_address', 'Jl. Testing Automation No. 1')
                    ->type('warehouse_phone', '0812999888')

                    // Klik label checkbox agar toggle berubah status
                    ->click('label[for="is_rm_warehouse"]')
                    
                    ->press('Simpan')
                    ->waitForLocation('/warehouses')
                    
                    // Verifikasi Visual
                    ->assertPathIs('/warehouses')
                    ->assertSee($warehouseName)
                    ->assertPresent('.alert-success');
            
            // Verifikasi Database
            $this->assertDatabaseHas('warehouses', [
                'warehouse_name'    => $warehouseName,
                'warehouse_address' => 'Jl. Testing Automation No. 1',
                'warehouse_phone'   => '0812999888',
                'is_active'         => true, 
                'is_rm_warehouse'   => true, 
                'is_fg_warehouse'   => false,
            ]);
        });
    }

     /**
     * Test Case 3: Validasi Form (Nama Kosong)
     */
    public function test_create_warehouse_validation_error()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/warehouses/create')
                    // Isi data lain, TAPI kosongkan 'warehouse_name'
                    ->type('warehouse_address', 'Alamat Valid')
                    ->type('warehouse_phone', '0812345')
                    
                    ->press('Simpan')
                    
                    // 1. Pastikan sistem MENOLAK (Tetap di halaman create)
                    ->assertPathIs('/warehouses/create')
                    
                    // 2. Pastikan ada elemen error berwarna merah (apapun tulisannya)
                    // Class '.text-danger' ada di file form.blade.php kamu
                    ->assertPresent('.text-danger');
        });
    }
}