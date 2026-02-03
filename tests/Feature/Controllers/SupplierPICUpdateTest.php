<?php

namespace Tests\Feature\Controllers;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use App\Models\User;
use App\Models\SupplierPic;

class SupplierPICUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Config agar Model SupplierPic membaca tabel yang kita buat
        Config::set('db_tables.supplier_pic', 'supplier_pic');

        // 2. Bersihkan tabel lama
        Schema::dropIfExists('supplier_pic');
        Schema::dropIfExists('supplier'); 
        Schema::dropIfExists('users');

        // 3. Buat tabel 'supplier'
        Schema::create('supplier', function (Blueprint $table) {
            $table->id();
            $table->string('supplier_id')->index(); 
            $table->string('company_name')->nullable();
            $table->timestamps();
        });

        // 4. Buat tabel 'supplier_pic'
        Schema::create('supplier_pic', function (Blueprint $table) {
            $table->id(); // Auto increment integer
            $table->string('supplier_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('position')->nullable();
            $table->date('assigned_date')->nullable();
            $table->timestamps();
        });

        // 5. Buat tabel user
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

    /** @test */
    public function it_can_update_supplier_pic_detail_successfully()
    {
        $user = User::factory()->create();

        // ARRANGE
        $supplierId = 'SUP001';
        DB::table('supplier')->insert(['supplier_id' => $supplierId, 'company_name' => 'PT Testing']);
        
        $pic = new SupplierPic();
        // PERBAIKAN PENTING: Set ID manual karena Model Anda 'incrementing = false'
        $pic->id = 1; 
        $pic->supplier_id = $supplierId;
        $pic->name = 'Budi Awal';
        $pic->email = 'budi@example.com';
        $pic->phone_number = '08123456789';
        $pic->assigned_date = '2023-01-01';
        $pic->save();

        $updateData = [
            'supplier_id' => $supplierId,
            'name' => 'Budi Update',
            'email' => 'budi.new@example.com',
            'phone_number' => '089999999',
            'assigned_date' => '2023-12-31',
            'position' => 'Manager'
        ];

        // ACT: Pastikan URL memiliki ID (contoh: /supplier-pic/update/1)
        $response = $this->actingAs($user)
                         ->put("/supplier-pic/update/{$pic->id}", $updateData);

        // ASSERT
        $response->assertStatus(200)
                 ->assertJson(['status' => 'success']);

        $this->assertDatabaseHas('supplier_pic', [
            'id' => 1,
            'name' => 'Budi Update',
            'email' => 'budi.new@example.com'
        ]);
    }

    /** @test */
    public function it_fails_validation_when_required_fields_are_empty()
    {
        $user = User::factory()->create();
        
        $supplierId = 'SUP001';
        DB::table('supplier')->insert(['supplier_id' => $supplierId]);

        $pic = new SupplierPic();
        $pic->id = 2; // Set ID manual
        $pic->supplier_id = $supplierId;
        $pic->name = 'Siti';
        $pic->email = 'siti@example.com';
        $pic->phone_number = '08111';
        $pic->assigned_date = '2023-01-01';
        $pic->save();

        // ACT
        $response = $this->actingAs($user)
                         ->put("/supplier-pic/update/{$pic->id}", [
                             'name' => '',
                             'email' => '',
                         ]);

        // ASSERT
        $response->assertStatus(422)
                 ->assertJsonStructure(['errors']);
    }
}