<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_api_returns_correct_structure()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $warehouse = Warehouse::create(['name' => 'Gudang Pusat', 'code' => 'WH-01']);
        
        // Previously this tested /api/dashboard. If the routes changed, we might need to update this, 
        // but let's keep the existing logic from PhaseTwoFeatureTest.
        $response = $this->actingAs($user)->getJson('/api/dashboard');
        
        $response->assertStatus(200)
            ->assertJsonStructure([
                'trends',
                'warehouses',
                'low_stock_alerts',
                'total_products',
                'total_warehouses'
            ]);
    }
}
