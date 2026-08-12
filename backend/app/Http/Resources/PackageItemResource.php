<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'trackingNo' => $this->tracking_no,
            'recipientUnit' => $this->recipient_unit,
            'recipientName' => $this->recipient_name,
            'courier' => $this->courier,
            'status' => $this->status,
            'arrivedAt' => $this->arrived_at?->toIso8601String(),
            'collectedAt' => $this->collected_at?->toIso8601String(),
        ];
    }
}
