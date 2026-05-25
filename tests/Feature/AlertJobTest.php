<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Notification;
use App\Notifications\LowStockAlert;

class AlertJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_low_stock_alert_is_sent_when_stock_drops_below_minimum()
    {
        Notification::fake();

        $user = User::factory()->create(['role' => 'manager']);
        $admin = User::factory()->create(['role' => 'admin']);
        
        $warehouse = Warehouse::create(['name' => 'Gudang Pusat', 'code' => 'WH-01']);
        $category = Category::create(['name' => 'Elektronik']);
        $product = Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'warehouse_id' => $warehouse->id,
            'stock' => 15,
            'min_stock' => 10
        ]);

        $this->actingAs($user);

        // Transaction out that makes stock drop to 5 (below 10)
        $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 10
        ])->assertStatus(201);

        Notification::assertSentTo(
            [$admin, $user],
            LowStockAlert::class,
            function ($notification, $channels) use ($product) {
                return $notification->product->id === $product->id;
            }
        );
    }
}
