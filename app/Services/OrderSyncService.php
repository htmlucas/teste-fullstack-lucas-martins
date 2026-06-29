<?php

namespace App\Services;

use App\Clients\FakeStoreClient;
use App\Jobs\ProcessOrdersPageJob;

class OrderSyncService
{
    public function __construct(private FakeStoreClient $client){}

    public function dispatchImportJobs(): int
    {
        $carts = $this->client->getCarts();

        $chunks = collect($carts)->chunk(20);

        $chunks->each(function ($page, $index) {
            ProcessOrdersPageJob::dispatch(
                carts: $page->values()->toArray(),
                page: $index + 1
            );
        });

        return $chunks->count();
        
    }

}