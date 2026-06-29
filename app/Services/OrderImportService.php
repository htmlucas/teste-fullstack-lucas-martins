<?php

namespace App\Services;

use App\Models\Affiliate;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderImportService
{
    public function import(array $carts): void
    {
        DB::transaction(function () use ($carts) {
            foreach ($carts as $cart) {
                $this->importCart($cart);
            }
        });
    }

    private function importCart(array $cart): void
    {
        $affiliate = Affiliate::where('external_id', $cart['userId'])->firstOrFail();

        $items = collect($cart['products'] ?? []);

        $products = Product::whereIn('external_id', $items->pluck('productId'))
            ->get()
            ->keyBy('external_id');

        $total = $items->sum(function ($item) use ($products) {
            $product = $products->get($item['productId']);

            return $product
                ? $product->price * $item['quantity']
                : 0;
        });

        $order = Order::updateOrCreate(
            ['external_id' => $cart['id']],
            [
                'affiliate_id' => $affiliate->id,
                'status' => 'pending',
                'total' => $total,
                'ordered_at' => ! empty($cart['date']) ? Carbon::parse($cart['date'])->toDateTimeString(): null,
            ]
        );

        foreach ($items as $item) {
            $product = $products->get($item['productId']);

            if (! $product) {
                continue;
            }

            $order = Order::firstOrNew([
                'external_id' => $cart['id'],
            ]);

            if (! $order->exists) {
                $order->status = 'pending';
            }

            $order->affiliate_id = $affiliate->id;
            $order->total = $total;
            $order->ordered_at = ! empty($cart['date'])
                ? Carbon::parse($cart['date'])->toDateTimeString()
                : null;

            $order->save();
        }
    }
}