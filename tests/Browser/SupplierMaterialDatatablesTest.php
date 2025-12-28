<?php

namespace Tests\Browser;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

class SupplierMaterialDatatablesTest extends DuskTestCase
{
    use DatabaseMigrations;

    /**
     * Setup Database Manual (Mengakali Migration yang Hilang/Custom)
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Buat Tabel USERS (Darurat karena file migration user tidak ada )
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email')->unique();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamps();
            });
        }

        // 2. Buat Tabel SUPPLIER_PRODUCT (Sesuai file migration yang kamu kirim)
        // Kita hardcode nama tabel 'supplier_product' agar aman
        if (!Schema::hasTable('supplier_product')) {
            Schema::create('supplier_product', function (Blueprint $table) {
                $table->id();
                $table->char('supplier_id', 6); // Sesuai migration kamu
                $table->string('company_name', 100); // Ternyata nama PT ada di tabel ini juga
                $table->char('product_id', 50);
                $table->string('product_name', 50);
                $table->integer('base_price');
                $table->timestamps();
            });
        }
    }

    /**
     * Skenario 1: Pastikan halaman bisa dibuka dan judulnya benar.
     */
    public function test_user_can_access_page()
    {
        $user = User::factory()->create();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    // URL hardcoded sesuai href di sidebar blade kamu: /supplier/material/list
                    ->visit('/supplier/material/list') 
                    // Assert teks sesuai <h3 class="mb-0 me-2">Supplier Material</h3>
                    ->assertSee('Supplier Material') 
                    ->assertSee('List Table');
        });
    }

    /**
     * Skenario 2: Pastikan Data muncul di Tabel.
     */
    public function test_table_shows_data()
    {
        $user = User::factory()->create();

        // Input Data Dummy Manual ke database
        // Kita pakai DB::table agar tidak tergantung Model Factory yang mungkin belum ada
        DB::table('supplier_product')->insert([
            'supplier_id'   => 'SUP001',
            'company_name'  => 'PT Mencari Cinta Sejati',
            'product_id'    => 'PROD-01',
            'product_name'  => 'Besi Beton Ulir',
            'base_price'    => 50000,
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                    ->visit('/supplier/material/list')
                    ->pause(1000)
                    // Cek apakah data dummy tadi muncul di layar
                    ->assertSee('PT Mencari Cinta Sejati')
                    ->assertSee('Besi Beton Ulir')
                    ->assertSee('50000');
        });
    }
}