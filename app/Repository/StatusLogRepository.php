<?php

namespace App\Repository;

use App\Models\OrderStatusLog;

class StatusLogRepository
{
    public function __construct(
        private OrderStatusLog $entity
    ) {}

    public function create(array $data): OrderStatusLog
    {
        return $this->entity->create($data);
    }
}