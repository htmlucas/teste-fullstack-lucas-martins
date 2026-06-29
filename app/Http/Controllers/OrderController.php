<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\ListOrdersRequest;

class OrderController extends Controller
{
    use ApiResponse;

    public function __construct(private OrderService $orderService){}

    public function index(ListOrdersRequest $request): JsonResponse
    {
        $orders = $this->orderService->paginate(
            $request->validated()
        );

        return $this->success(
            OrderResource::collection($orders),
            [
                'current_page' => $orders->currentPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
                'last_page' => $orders->lastPage(),
            ]
        );
    }

    public function find(int $id): JsonResponse
    {
        $order = $this->orderService->find($id);

        return $this->success(new OrderResource($order));
    }

    public function metrics(): JsonResponse
    {
        return $this->success($this->orderService->getMetrics());
    }

    public function status(UpdateOrderStatusRequest $request, int $id): JsonResponse
    {
        $validated = $request->validated();

        $order = $this->orderService->updateStatus($id,$validated['status']);

        return $this->success(new OrderResource($order));
    }
}
