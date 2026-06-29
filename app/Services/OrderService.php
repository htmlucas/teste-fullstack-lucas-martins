<?php

namespace App\Services;

use App\Models\Order;
use App\Repository\OrderRepository;
use App\Repository\StatusLogRepository;
use App\Support\OrderStateMachine;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function __construct(
        private OrderRepository $repository,
        private StatusLogRepository $statusLogRepository
    ) {}

    public function paginate(array $filters): LengthAwarePaginator
    {
        return $this->repository->paginate($filters);
    }

    public function find(int $id): Order
    {
        $order = $this->repository->findWithDetails($id);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Pedido não encontrado.',
            ]);
        }

        return $order;
    }

    public function updateStatus(int $id, string $newStatus): Order
    {
        $order = $this->repository->findById($id);

        if (! $order) {
            throw ValidationException::withMessages([
                'order' => 'Pedido não encontrado.',
            ]);
        }

        $this->validateTransition($order->status, $newStatus);

        return DB::transaction(function () use ($order, $newStatus) {
            $oldStatus = $order->status;

            $updatedOrder = $this->repository->updateStatus($order, $newStatus);

            $this->statusLogRepository->create([
                'order_id' => $updatedOrder->id,
                'from_status' => $oldStatus,
                'to_status' => $newStatus,
                'user_id' => auth()->id(),
                'changed_at' => now(),
            ]);

            $this->resetMetricsCache();

            return $this->repository->findWithDetails($updatedOrder->id);
        });

        return $order->refresh();
    }

    public function getMetrics(): array
    {
        return Cache::remember(
            'orders_metrics',
            now()->addMinutes(5),
            fn () => $this->repository->metrics()
        );
    }

    private function validateTransition(string $current, string $next): void
    {
        if (! OrderStateMachine::canTransition($current, $next)) {
            throw ValidationException::withMessages([
                'status' => "Transição inválida de {$current} para {$next}.",
            ]);
        }
    }

    private function resetMetricsCache(): void
    {
        Cache::forget('orders_metrics');
    }
}