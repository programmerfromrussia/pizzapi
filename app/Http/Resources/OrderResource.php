<?php

namespace App\Http\Resources;

use App\Http\Resources\OrderItemResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total_price' => $this->orderItems->sum(function ($item) {
                return $item->price * $item->quantity;
            }),
            'location' => [
                'address' => $this->location->address,
                'city' => $this->location->city,
                'country' => $this->location->country,
            ],
            'items' => OrderItemResource::collection($this->orderItems),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at->format('Y-m-d H:i:s'),
        ];
    }
}
