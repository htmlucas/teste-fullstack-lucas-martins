<?php

namespace Tests\Unit;

use App\Models\Affiliate;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OrderStatusTransitionTest extends TestCase
{
    use RefreshDatabase;

    public function test_allows_valid_status_transition(): void
    {
        $affiliate = Affiliate::create([
            'external_id' => 1,
            'username' => 'johnd',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'external_id' => 1,
            'affiliate_id' => $affiliate->id,
            'status' => 'pending',
            'total' => 100,
            'ordered_at' => now(),
        ]);

        $service = app(OrderService::class);

        $updatedOrder = $service->updateStatus($order->id, 'approved');

        $this->assertEquals('approved', $updatedOrder->status);

        $this->assertDatabaseHas('order_status_logs', [
            'order_id' => $order->id,
            'from_status' => 'pending',
            'to_status' => 'approved',
        ]);
    }

    public function test_blocks_invalid_status_transition(): void
    {
        $affiliate = Affiliate::create([
            'external_id' => 1,
            'username' => 'johnd',
            'email' => 'john@example.com',
            'password' => 'secret',
        ]);

        $order = Order::create([
            'external_id' => 1,
            'affiliate_id' => $affiliate->id,
            'status' => 'cancelled',
            'total' => 100,
            'ordered_at' => now(),
        ]);

        $service = app(OrderService::class);

        $this->expectException(ValidationException::class);

        $service->updateStatus($order->id, 'approved');
    }
}