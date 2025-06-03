<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class failResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'success' => false,
            'message' => $this->resource ?? 'حدث خطأ ما', // نأخذ الرسالة من المتغير resource أو نعطي رسالة افتراضية
        ];
    }
}
