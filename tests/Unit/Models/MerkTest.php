<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Merk;
use App\Constants\MerkColumns;

class MerkTest extends TestCase
{
    use RefreshDatabase;

    // ========================================================================
    // TEST UNTUK FUNGSI getMerkById($id) 
    // ========================================================================

    /**
     * Test getMerkById returns correct merk when ID exists
     * @test
     */
    public function test_getMerkById_returns_correct_merk_when_id_exists()
    {
        // Arrange
        $merk = Merk::factory()->create([
            MerkColumns::MERK => 'Toyota',
            MerkColumns::IS_ACTIVE => true
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($merk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertInstanceOf(Merk::class, $result);
        $this->assertEquals($merk->id, $result->id);
        $this->assertEquals('Toyota', $result->{MerkColumns::MERK});
    }

    /**
     * Test getMerkById returns null when ID does not exist
     * @test
     */
    public function test_getMerkById_returns_null_when_id_does_not_exist()
    {
        // Arrange
        $nonExistentId = 99999;

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($nonExistentId);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getMerkById returns merk with correct data structure
     * @test
     */
    public function test_getMerkById_returns_correct_data_structure()
    {
        // Arrange
        $merk = Merk::factory()->create([
            MerkColumns::MERK => 'Honda',
            MerkColumns::IS_ACTIVE => true
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($merk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertNotNull($result->id);
        $this->assertNotNull($result->{MerkColumns::MERK});
        $this->assertNotNull($result->{MerkColumns::IS_ACTIVE});
        $this->assertNotNull($result->created_at);
        $this->assertNotNull($result->updated_at);
    }

    /**
     * Test getMerkById handles zero ID
     * @test
     */
    public function test_getMerkById_handles_zero_id()
    {
        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById(0);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getMerkById handles negative ID
     * @test
     */
    public function test_getMerkById_handles_negative_id()
    {
        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById(-1);

        // Assert
        $this->assertNull($result);
    }

    /**
     * Test getMerkById works with active merk
     * @test
     */
    public function test_getMerkById_works_with_active_merk()
    {
        // Arrange
        $activeMerk = Merk::factory()->active()->create([
            MerkColumns::MERK => 'Suzuki'
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($activeMerk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertTrue((bool)$result->{MerkColumns::IS_ACTIVE});
        $this->assertEquals('Suzuki', $result->{MerkColumns::MERK});
    }

    /**
     * Test getMerkById works with inactive merk
     * @test
     */
    public function test_getMerkById_works_with_inactive_merk()
    {
        // Arrange
        $inactiveMerk = Merk::factory()->inactive()->create([
            MerkColumns::MERK => 'Daihatsu'
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($inactiveMerk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertFalse((bool)$result->{MerkColumns::IS_ACTIVE});
        $this->assertEquals('Daihatsu', $result->{MerkColumns::MERK});
    }

    /**
     * Test getMerkById returns merk with all attributes
     * @test
     */
    public function test_getMerkById_returns_merk_with_all_attributes()
    {
        // Arrange
        $merk = Merk::factory()->create([
            MerkColumns::MERK => 'Yamaha',
            MerkColumns::IS_ACTIVE => true
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($merk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($merk->id, $result->id);
        $this->assertEquals($merk->{MerkColumns::MERK}, $result->{MerkColumns::MERK});
        $this->assertEquals($merk->{MerkColumns::IS_ACTIVE}, $result->{MerkColumns::IS_ACTIVE});
        $this->assertEquals($merk->created_at->timestamp, $result->created_at->timestamp);
        $this->assertEquals($merk->updated_at->timestamp, $result->updated_at->timestamp);
    }

    /**
     * Test getMerkById with string ID (type coercion)
     * @test
     */
    public function test_getMerkById_handles_string_id()
    {
        // Arrange
        $merk = Merk::factory()->create([
            MerkColumns::MERK => 'Mitsubishi'
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById((string)$merk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($merk->id, $result->id);
        $this->assertEquals('Mitsubishi', $result->{MerkColumns::MERK});
    }

    /**
     * Test getMerkById returns specific merk when multiple exist
     * @test
     */
    public function test_getMerkById_returns_specific_merk_when_multiple_exist()
    {
        // Arrange
        $merk1 = Merk::factory()->create([MerkColumns::MERK => 'Kawasaki']);
        $merk2 = Merk::factory()->create([MerkColumns::MERK => 'Mazda']);
        $merk3 = Merk::factory()->create([MerkColumns::MERK => 'Nissan']);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($merk2->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals($merk2->id, $result->id);
        $this->assertEquals('Mazda', $result->{MerkColumns::MERK});
        $this->assertNotEquals($merk1->id, $result->id);
        $this->assertNotEquals($merk3->id, $result->id);
    }

    /**
     * Test getMerkById with freshly created merk
     * @test
     */
    public function test_getMerkById_works_with_freshly_created_merk()
    {
        // Arrange
        $newMerk = Merk::factory()->create([
            MerkColumns::MERK => 'Isuzu',
            MerkColumns::IS_ACTIVE => true
        ]);

        // Act - immediately fetch
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($newMerk->id);

        // Assert
        $this->assertNotNull($result);
        $this->assertEquals('Isuzu', $result->{MerkColumns::MERK});
        $this->assertTrue((bool)$result->{MerkColumns::IS_ACTIVE});
    }

    /**
     * Test getMerkById returns instance with appended attributes
     * @test
     */
    public function test_getMerkById_returns_instance_with_appended_attributes()
    {
        // Arrange
        $merk = Merk::factory()->active()->create([
            MerkColumns::MERK => 'Lexus'
        ]);

        // Act
        $merkInstance = new Merk();
        $result = $merkInstance->getMerkById($merk->id);

        // Assert
        $this->assertNotNull($result);
        
        // Check appended attributes (dari getStatusLabelAttribute & getDisplayNameAttribute)
        $this->assertEquals('Aktif', $result->status_label);
        $this->assertStringContainsString('Lexus', $result->display_name);
        $this->assertStringContainsString('', $result->display_name);
    }
}