<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'order_number'    => $this->order_number,
            'status'          => $this->status,
            'order_type'      => $this->order_type,
            'table_number'    => $this->table?->table_number,
            'guest_count'     => $this->guest_count,
            'subtotal'        => (float) $this->subtotal,
            'discount_amount' => (float) $this->discount_amount,
            'total_amount'    => (float) $this->total_amount,
            'notes'           => $this->notes,
            'created_at'      => $this->created_at->toIso8601String(),
            'items'           => $this->items->map(fn ($it) => [
                'item_id'              => $it->menu_item_id,
                'name'                 => $it->menuItem?->name,
                'quantity'             => $it->quantity,
                'unit_price'           => (float) $it->unit_price,
                'discounted_price'     => (float) $it->discounted_price,
                'subtotal'             => (float) $it->subtotal,
                'special_instructions' => $it->special_instructions,
                'status'               => $it->status,
            ]),
        ];
    }
}
