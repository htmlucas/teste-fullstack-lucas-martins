<?php

namespace App\Repository;


use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use App\Models\Order;

class OrderRepository
{
    public function __construct(private Order $entity){}

    public function findById(int $id): ?Order
    {
        return $this->entity->find($id);
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDir = $filters['sort_dir'] ?? 'desc';

        return $this->entity
            ->newQuery()
            ->with(['affiliate'])
            ->when(! empty($filters['affiliate_id']), function ($query) use ($filters) {
                $query->where('affiliate_id', $filters['affiliate_id']);
            })
            ->when(! empty($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->when(! empty($filters['date_from']), function ($query) use ($filters) {
                $query->whereDate('ordered_at', '>=', $filters['date_from']);
            })
            ->when(! empty($filters['date_to']), function ($query) use ($filters) {
                $query->whereDate('ordered_at', '<=', $filters['date_to']);
            })
            ->when(! empty($filters['min_value']), function ($query) use ($filters) {
                $query->where('total', '>=', $filters['min_value']);
            })
            ->when(! empty($filters['max_value']), function ($query) use ($filters) {
                $query->where('total', '<=', $filters['max_value']);
            })
            ->orderBy($sortBy, $sortDir)
            ->paginate(20)
            ->withQueryString();
    }

    public function metrics(): array
    {
        return [
            'total_orders' => $this->entity->count(),

            'total_revenue' => $this->entity->where('status', 'approved')
                ->sum('total'),

            'average_ticket' => $this->entity->where('status', 'approved')
                ->avg('total'),

            'pending_orders' => $this->entity->where('status', 'pending')
                ->count(),

            'approved_orders' => $this->entity->where('status', 'approved')
                ->count(),

            'cancelled_orders' => $this->entity->where('status', 'cancelled')
                ->count(),

            'refunded_orders' => $this->entity->where('status', 'refunded')
                ->count(),
        ];
    }

    public function updateStatus(Order $order,string $status): Order {

        $order->update([
            'status' => $status,
        ]);

        return $order;
    }

    public function findWithDetails(int $id): ?Order
    {
        return $this->entity
            ->newQuery()
            ->with([
                'affiliate',
                'items.product',
                'statusLogs.user',
            ])
            ->find($id);
    }
}