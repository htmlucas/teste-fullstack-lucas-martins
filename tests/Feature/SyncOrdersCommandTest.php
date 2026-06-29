<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SyncOrdersCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_sync_command_imports_orders(): void
    {
        Http::fake([
            'https://fakestoreapi.com/users*' => Http::response([
                [
                    'id' => 1,
                    'username' => 'johnd',
                    'email' => 'john@example.com',
                    'password' => 'secret',
                ],
            ], 200),

            'https://fakestoreapi.com/products*' => Http::response([
                [
                    'id' => 1,
                    'title' => 'Produto teste',
                    'price' => 100.00,
                    'category' => 'test',
                    'description' => 'Produto fake',
                    'image' => 'https://example.com/image.jpg',
                ],
            ], 200),

            'https://fakestoreapi.com/carts*' => Http::response([
                [
                    'id' => 1,
                    'userId' => 1,
                    'date' => '2024-01-01',
                    'products' => [
                        [
                            'productId' => 1,
                            'quantity' => 2,
                        ],
                    ],
                ],
            ], 200),
        ]);

        $this->artisan('orders:sync')
            ->assertExitCode(0);

        $this->assertDatabaseHas('affiliates', [
            'external_id' => 1,
            'username' => 'johnd',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('products', [
            'external_id' => 1,
        ]);

        $this->assertDatabaseHas('orders', [
            'external_id' => 1,
        ]);

        $order = Order::where('external_id', 1)->first();

        $this->assertNotNull($order);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_id' => 1,
            'quantity' => 2,
        ]);
    }
}