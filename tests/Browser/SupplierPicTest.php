<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SupplierPicTest extends DuskTestCase
{
    /**
     * Setup data awal sebelum setiap test berjalan.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // 1. Config Table Names
        Config::set('db_tables.supplier_pic', 'supplier_pics');
        Config::set('db_tables.supplier', 'suppliers');
        
        // 2. Reset Database
        Artisan::call('migrate:fresh');

        // 3. Buat Tabel 'users' Manual 
        if (!Schema::hasTable('users')) {
            DB::statement('
                CREATE TABLE users (
                    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    email_verified_at TIMESTAMP NULL,
                    password VARCHAR(255) NOT NULL,
                    remember_token VARCHAR(100) NULL,
                    created_at TIMESTAMP NULL,
                    updated_at TIMESTAMP NULL
                )
            ');
        }

        // 4. Buat User Admin
        $userId = DB::table('users')->insertGetId([
            'name' => 'Admin Test',
            'email' => 'admin@erp.com',
            'password' => bcrypt('password'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->user = User::find($userId);

        // 5. Buat Data Supplier (Induk)
        DB::table('suppliers')->insert([
            'supplier_id' => 'SUP001', 
            'company_name' => 'PT. Sumber Makmur',
            'address' => 'Jl. Kaliurang Km 14, Yogyakarta',
            'telephone' => '08123456789', 
            'bank_account' => 'BCA 1234567890', 
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test 1: Pastikan User bisa login dan melihat halaman
     */
    public function test_user_can_access_supplier_pic_list_page()
    {
        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/supplier/pic/list')
                    ->assertSee('Daftar PIC Supplier') // Judul Halaman
                    ->assertSee('List Table'); // Judul Tabel
        });
    }

    /**
     * Test 2: Pastikan data PIC muncul di tabel
     */
    public function test_user_can_see_pic_data_in_table()
    {
        // Insert Data Dummy
        DB::table('supplier_pics')->insert([
            'supplier_id' => 'SUP001',
            'name' => 'Budi Santoso',
            'email' => 'budi@sumbermakmur.com',
            'phone_number' => '08123456789',
            'assigned_date' => now()->subYear(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/supplier/pic/list')
                    ->assertSee('SUP001')
                    ->assertSee('Budi Santoso')
                    ->assertSee('budi@sumbermakmur.com');
        });
    }

    /**
     * Test 3: Pastikan Pagination Berjalan (Next Page)
     */
    public function test_pagination_is_working()
    {
        // Insert 15 data agar muncul pagination (limit 10)
        for ($i = 1; $i <= 15; $i++) {
            DB::table('supplier_pics')->insert([
                'supplier_id' => 'SUP001',
                'name' => "PIC User $i",
                'email' => "pic$i@test.com",
                'phone_number' => "08000000$i",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->user)
                    ->visit('/supplier/pic/list')
                    ->assertSee('PIC User 1') // Ada di halaman 1
                    ->assertDontSee('PIC User 15') // Tidak ada di halaman 1
                    ->scrollIntoView('.pagination') // Scroll ke bawah
                    ->clickLink('2') // Klik halaman 2
                    ->assertSee('PIC User 15'); // Muncul di halaman 2
        });
    }

    /**
     * Test 4: Pastikan Tombol Hapus memunculkan Konfirmasi (SweetAlert)
     */
    public function test_delete_button_shows_confirmation_alert()
    {
        $uniqueId = 999; 
        
        DB::table('supplier_pics')->insert([
            'id' => $uniqueId,
            'supplier_id' => 'SUP001',
            'name' => 'PIC To Delete',
            'email' => 'delete@test.com',
            'phone_number' => '08999999999',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->browse(function (Browser $browser) use ($uniqueId) {
            $browser->loginAs($this->user)
                    ->visit('/supplier/pic/list')
                    ->assertSee('PIC To Delete')
                    // Klik tombol delete
                    ->click("form[action*='/supplier/pic/delete/{$uniqueId}'] button")
                    // Cek apakah SweetAlert muncul
                    ->waitForText('Yakin ingin menghapus?')
                    ->assertSee('Data PIC "PIC To Delete" akan dihapus permanen!')
                    // Klik Ya
                    ->press('Ya, hapus!')
                    ->pause(1000)
                    ->assertPathIs('/supplier/pic/list');
            //cek
            // Cek database apakah data hilang
            $this->assertDatabaseMissing('supplier_pics', ['id' => $uniqueId]);
        });
    }
}