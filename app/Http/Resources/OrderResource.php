<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Order
*/
class OrderResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => (float) $this->total,
            'ordered_at' => $this->ordered_at?->toISOString(),
            'affiliate' => $this->whenLoaded('affiliate', function () {
                return [
                    'id' => $this->affiliate->id,
                    'username' => $this->affiliate->username,
                    'email' => $this->affiliate->email,
                ];
            }),
            'items' => OrderItemResource::collection(
                $this->whenLoaded('items')
            ),
            'status_logs' => OrderStatusLogResource::collection(
                $this->whenLoaded('statusLogs')
            ),

            'external_id' => $this->external_id,
            'affiliate_id' => $this->affiliate_id,



            
            
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
