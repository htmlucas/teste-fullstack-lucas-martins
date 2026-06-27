<?php
namespace App\Services;

use App\Clients\FakeStoreClient;
use App\Repository\AffiliateRepository;

class AffiliateSyncService
{

    public function __construct(private FakeStoreClient $client,private AffiliateRepository $repository,) {}

    public function sync(): void
    {
        $users = $this->client->getUsers();

        foreach ($users as $user) {
            $this->repository->upsert($user);
        }
        
    }
}