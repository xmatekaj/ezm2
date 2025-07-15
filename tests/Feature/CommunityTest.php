<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Community;

class CommunityTest extends TestCase
{
    use RefreshDatabase;

    public function test_community_can_be_created_with_minimal_data(): void
    {
        $community = Community::create([
            'name' => 'Test Community',
            'full_name' => 'Test Community Full Name',
            'address_street' => 'ul. Testowa 1',
            'address_postal_code' => '00-001',
            'address_city' => 'Warszawa',
            'address_state' => 'mazowieckie',
            'is_active' => true,
            'color' => '#000000',
        ]);

        $this->assertDatabaseHas('communities', [
            'name' => 'Test Community',
            'regon' => null,
            'tax_id' => null,
            'total_area' => null,
        ]);
    }

    public function test_community_completion_percentage(): void
    {
        $community = Community::create([
            'name' => 'Test',
            'full_name' => 'Test Full',
            'address_street' => 'ul. Test 1',
            'address_postal_code' => '00-001',
            'address_city' => 'Test',
            'address_state' => 'test',
            'regon' => '123456789',
            'tax_id' => '1234567890',
            'total_area' => 1000,
            'apartments_area' => 800,
            'apartment_count' => 10,
            'staircase_count' => 1,
            'is_active' => true,
            'color' => '#000000',
        ]);

        $this->assertEquals(100, $community->completion_percentage);
    }

    public function test_regon_and_nip_are_optional(): void
    {
        $community = Community::create([
            'name' => 'Test Community',
            'full_name' => 'Test Community Full Name',
            'address_street' => 'ul. Testowa 1',
            'address_postal_code' => '00-001',
            'address_city' => 'Warszawa',
            'address_state' => 'mazowieckie',
            'is_active' => true,
            'color' => '#000000',
        ]);

        $this->assertNull($community->regon);
        $this->assertNull($community->tax_id);
        $this->assertTrue($community->hasMinimumData());
    }
}