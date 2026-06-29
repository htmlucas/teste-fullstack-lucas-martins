<?php

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class OrdersMetricTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_order_metrics_without_cache(): void
    {
        Cache::flush();

        $affiliate = Affiliate::create([
            'external_id' => 1,
            'username' => 'johnd',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        Order::create([
            'external_id' => 1,
            'affiliate_id' => $affiliate->id,
            'status' => 'approved',
            'total' => 100,
            'ordered_at' => now(),
        ]);

        Order::create([
            'external_id' => 2,
            'affiliate_id' => $affiliate->id,
            'status' => 'cancelled',
            'total' => 50,
            'ordered_at' => now(),
        ]);

        $response = $this->getJson('/api/orders/metrics');

        $response->assertOk()
            ->assertJsonStructure([
                'data',
            ]);
    }

    public function test_returns_order_metrics_with_cache(): void
    {
        Cache::put('orders_metrics', [
            'total_orders' => 99,
            'approved_orders' => 50,
            'cancelled_orders' => 10,
            'gross_revenue' => 1000,
            'net_revenue' => 800,
        ], now()->addMinutes(5));

        $response = $this->getJson('/api/orders/metrics');

        $response->assertOk()
            ->assertJson([
                'data' => [
                    'total_orders' => 99,
                    'approved_orders' => 50,
                    'cancelled_orders' => 10,
                    'gross_revenue' => 1000,
                    'net_revenue' => 800,
                ],
            ]);
    }
}