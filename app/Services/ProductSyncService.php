<?php
namespace App\Services;

use App\Clients\FakeStoreClient;
use App\Repository\ProductRepository;

class ProductSyncService
{

    public function __construct(private FakeStoreClient $client,private ProductRepository $repository,) {}

    public function sync(): void
    {
        $products = $this->client->getProducts();

        foreach($products as $product) {
            $this->repository->upsert($product);
        }
        
    }
}