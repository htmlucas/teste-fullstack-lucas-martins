<?php

namespace App\Http\Controllers;

use App\Http\Resources\AffiliateSummaryResource;
use App\Services\AffiliateService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AffiliatesController extends Controller
{
    use ApiResponse;

    public function __construct(
        private AffiliateService $affiliateService
    ) {}

    public function summary(int $id): JsonResponse
    {
        $summary = $this->affiliateService->summary($id);

        return $this->success(
            new AffiliateSummaryResource($summary)
        );
    }
}