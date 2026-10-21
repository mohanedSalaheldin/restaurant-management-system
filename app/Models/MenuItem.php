<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_id',
        'category_id',
        'subcategory_id',
        'name',
        'description',
        'price',
        'image',
        'preparation_time',
        'availability',
        'special_tags',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'preparation_time' => 'integer',
        'special_tags' => 'array',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function offers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Offer::class, 'offer_menu_item')->withTimestamps();
    }

    public function getActiveOfferAttribute(): ?Offer
    {
        return $this->offers->first(fn($offer) => $offer->isValidNow());
    }

    public function getFinalPriceAttribute(): float
    {
        $activeOffer = $this->active_offer;
        return $activeOffer ? $activeOffer->calculateDiscountedPrice((float) $this->price) : (float) $this->price;
    }
}
