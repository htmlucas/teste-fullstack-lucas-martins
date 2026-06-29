<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use App\Services\OrderImportService;

class ProcessOrdersPageJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function backoff(): array
    {
        return [10, 30, 60];
    }

    public function __construct( private readonly array $carts,private readonly int $page){}

    /**
     * Execute the job.
     */
    public function handle(OrderImportService $service): void
    {
        $service->import($this->carts);
    }
}
