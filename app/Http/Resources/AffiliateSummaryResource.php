<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AffiliateSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'affiliate' => $this->resource['affiliate'],
            'total_orders' => $this->resource['total_orders'],
            'total_revenue' => $this->resource['total_revenue'],
            'average_ticket' => $this->resource['average_ticket'],
            'cancellation_rate' => $this->resource['cancellation_rate'],
        ];
    }
}
