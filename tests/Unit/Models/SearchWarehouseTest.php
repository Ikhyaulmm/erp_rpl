<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Warehouse;
use App\Http\Controllers\WarehouseController;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;

class SearchWarehouseTest extends TestCase
{
#[Test]
public function test_modelSearchWarehouse_returns_matching_results(): void
{
    // Tambah data contoh
    Warehouse::create([
        'warehouse_name' => 'Gudang dolores',
        'warehouse_code' => 'GD001',
        'warehouse_address' => 'Jl. Mawar No. 5',
    ]);

    $results = (new Warehouse)->searchWarehouse('dolores');

    $this->assertNotEmpty($results, 'Search result should not be empty');
    $this->assertTrue(
        $results->pluck('warehouse_name')->contains('Gudang dolores'),
        'Search result should contain warehouse with name "Gudang dolores"'
    );
}

}
