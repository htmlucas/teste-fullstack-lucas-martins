<?php

namespace App\Services;

use App\Repository\AffiliateRepository;
use Illuminate\Validation\ValidationException;

class AffiliateService
{
    public function __construct(
        private AffiliateRepository $affiliateRepository
    ) {}

    public function summary(int $id): array
    {
        $summary = $this->affiliateRepository->summary($id);

        if (! $summary) {
            throw ValidationException::withMessages([
                'affiliate' => 'Afiliado não encontrado.',
            ]);
        }

        return $summary;
    }
}