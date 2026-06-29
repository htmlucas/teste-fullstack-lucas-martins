<?php

namespace App\Console\Commands;

use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

#[Signature('queue:heartbeat')]
#[Description('Updates queue worker heartbeat for health checks')]
class QueueWorkerHeartbeatCommand extends Command
{

    public function handle(): int
    {
        $this->info('Queue worker heartbeat started.');

        // @phpstan-ignore-next-line
        while (true) {
            Cache::put(
                'queue_worker:last_seen',
                now()->toISOString(),
                now()->addSeconds(30)
            );

            sleep(10);
        }
    }
}
