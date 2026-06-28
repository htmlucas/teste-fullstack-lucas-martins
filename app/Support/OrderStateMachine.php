<?php

namespace App\Support;

class OrderStateMachine
{
    private const TRANSITIONS = [
        'pending' => ['approved', 'cancelled'],
        'approved' => ['refunded'],
        'cancelled' => [],
        'refunded' => [],
    ];

    public static function canTransition(string $from, string $to): bool
    {
        return in_array($to, self::TRANSITIONS[$from] ?? [], true);
    }

    public static function availableTransitions(string $status): array
    {
        return self::TRANSITIONS[$status] ?? [];
    }
}