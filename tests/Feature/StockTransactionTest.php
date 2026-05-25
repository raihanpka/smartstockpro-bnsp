<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\Category;
use App\Models\Product;

class StockTransactionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_record_inbound_transaction()
    {
        $user = User::factory()->create(['role' => 'manager']);
        $warehouse = Warehouse::create(['name' => 'Gudang Pusat', 'code' => 'WH-01']);
        $category = Category::create(['name' => 'Elektronik']);
        $product = Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'warehouse_id' => $warehouse->id,
            'stock' => 5,
            'min_stock' => 10
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'in',
            'quantity' => 20
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 25
        ]);
        $this->assertDatabaseHas('stock_transactions', [
            'product_id' => $product->id,
            'type' => 'in',
            'quantity' => 20
        ]);
    }

    public function test_outbound_fails_when_stock_insufficient()
    {
        $user = User::factory()->create(['role' => 'staff']);
        $warehouse = Warehouse::create(['name' => 'Gudang Pusat', 'code' => 'WH-01']);
        $category = Category::create(['name' => 'Elektronik']);
        $product = Product::create([
            'name' => 'Laptop',
            'sku' => 'LAP-001',
            'category_id' => $category->id,
            'warehouse_id' => $warehouse->id,
            'stock' => 5,
            'min_stock' => 10
        ]);

        $this->actingAs($user);

        $response = $this->postJson('/api/transactions', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'out',
            'quantity' => 10
        ]);

        $response->assertStatus(500); // Because we throw an exception in the service
    }
}
