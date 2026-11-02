<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $activeOffer = $this->active_offer;

        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'original_price'   => (float) $this->price,
            'final_price'      => (float) $this->final_price,
            'has_active_offer' => (bool) $activeOffer,
            'offer_details'    => $activeOffer ? [
                'name'          => $activeOffer->name,
                'discount_type' => $activeOffer->discount_type,
                'discount_val'  => (float) $activeOffer->discount_value,
                'valid_until'   => $activeOffer->end_date->format('Y-m-d'),
            ] : null,
            'image_url'        => $this->image ? asset('storage/' . $this->image) : null,
            'special_tags'     => $this->special_tags ?? [],
            'preparation_time' => $this->preparation_time ?? 15,
            'availability'     => $this->availability,
        ];
    }
}
