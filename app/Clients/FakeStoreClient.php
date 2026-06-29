<?php

namespace App\Clients;

use App\Exceptions\FakeStoreApiException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class FakeStoreClient
{

    public function __construct(private ?string $baseUrl = null)
    {
        $this->baseUrl ??= config('services.fakestore.base_url');
    }

    private function http(): PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->acceptJson()
            ->timeout(config('services.fakestore.timeout', 8))
            ->retry(3, 500);
    }

    private function get(string $endpoint): array
    {
        usleep(300_000); // 300ms entre chamadas
        
        $response = $this->http()->get($endpoint);

        if ($response->failed()) {
            throw new FakeStoreApiException(
                message: "FakeStore request failed: {$endpoint}",
                code: $response->status(),
                response: $response->json() ?? []
            );
        }

        return $response->json();
    }

    public function getUsers(): array
    {
        return $this->get('/users');
    }

    public function getProducts(): array
    {
        return $this->get('/products');
    }

    public function getCarts(): array
    {
        return $this->get('/carts');
    }
}