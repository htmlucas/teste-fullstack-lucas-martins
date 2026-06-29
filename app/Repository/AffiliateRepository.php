<?php

namespace App\Repository;

use App\Models\Affiliate;
use App\Models\Order;

class AffiliateRepository
{
    public function __construct(private Affiliate $entity, private Order $order){}


    public function summary(int $id): ?array
    {
        $affiliate = $this->entity->find($id);

        if (! $affiliate) {
            return null;
        }

        $orders = $this->order
            ->newQuery()
            ->where('affiliate_id', $affiliate->id);

        $totalOrders = (clone $orders)->count();

        $approvedOrders = (clone $orders)
            ->where('status', 'approved');

        $cancelledOrders = (clone $orders)
            ->where('status', 'cancelled')
            ->count();

        $totalRevenue = (clone $approvedOrders)->sum('total');

        $averageTicket = (clone $approvedOrders)->avg('total') ?? 0;

        $cancellationRate = $totalOrders > 0
            ? round(($cancelledOrders / $totalOrders) * 100, 2)
            : 0;

        return [
            'affiliate' => [
                'id' => $affiliate->id,
                'external_id' => $affiliate->external_id,
                'username' => $affiliate->username,
                'email' => $affiliate->email,
            ],
            'total_orders' => $totalOrders,
            'total_revenue' => (float) $totalRevenue,
            'average_ticket' => (float) $averageTicket,
            'cancellation_rate' => $cancellationRate,
        ];
    }

    public function upsert($user):void {
        $this->entity->updateOrCreate(
            ['external_id' => $user['id']],
            [
                'username' => $user['username'],
                'email' => $user['email'],
                'password' => $user['password'],
            ],
        );
    }

}