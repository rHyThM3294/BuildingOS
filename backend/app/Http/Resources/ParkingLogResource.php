<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 前端是 TypeScript/camelCase，這裡把 Eloquent 的 snake_case 欄位轉成
 * camelCase 輸出，讓 API 回傳的 JSON 直接對應 frontend/src/types/models.ts。
 */
class ParkingLogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'plateNumber' => $this->plate_number,
            'direction' => $this->direction,
            'status' => $this->status,
            'ownerName' => $this->owner_name,
            'recognizedAt' => $this->recognized_at?->toIso8601String(),
        ];
    }
}
