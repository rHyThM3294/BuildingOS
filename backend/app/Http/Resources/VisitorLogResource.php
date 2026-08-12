<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VisitorLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'visitorName' => $this->visitor_name,
            'visitorType' => $this->visitor_type,
            'targetUnit' => $this->target_unit,
            'status' => $this->status,
            'registeredAt' => $this->registered_at?->toIso8601String(),
            'notifiedAt' => $this->notified_at?->toIso8601String(),
        ];
    }
}
