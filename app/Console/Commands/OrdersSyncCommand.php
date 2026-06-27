<?php

namespace App\Console\Commands;

use App\Services\AffiliateSyncService;
use App\Exceptions\FakeStoreApiException;
use App\Services\OrderSyncService;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use App\Services\ProductSyncService;

#[Signature('orders:sync')]
#[Description('Sync orders from FakeStore API')]
class OrdersSyncCommand extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(AffiliateSyncService $affiliateSyncService,ProductSyncService $productSyncService,OrderSyncService $orderSyncService,): int
    {
         try {
            $this->info('Syncing affiliates...');
            $affiliateSyncService->sync();

            $this->info('Syncing products...');
            $productSyncService->sync();

            $this->info('Dispatching order import jobs...');
            $jobs = $orderSyncService->dispatchImportJobs();

            $this->info("{$jobs} order import jobs dispatched.");

            return self::SUCCESS;
        } catch (FakeStoreApiException $e) {
            report($e);

            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
