<?php

namespace App\Services;

use App\Models\Offer;

class OfferService
{
    public function createOffer(array $data, array $itemIds): Offer
    {
        $offer = Offer::create($data);
        $offer->items()->sync($itemIds);

        return $offer;
    }

    public function updateOffer(Offer $offer, array $data, array $itemIds): Offer
    {
        $offer->update($data);
        $offer->items()->sync($itemIds);

        return $offer;
    }

    public function deleteOffer(Offer $offer): void
    {
        $offer->items()->detach();
        $offer->delete();
    }
}
