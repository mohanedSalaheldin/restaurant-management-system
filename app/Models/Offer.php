<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Offer extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'discount_type',
        'discount_value',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'applicable_days',
        'status',
        'display_on_menu',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'applicable_days'  => 'array',
        'status'           => 'boolean',
        'display_on_menu'  => 'boolean',
    ];

    public function items(): BelongsToMany
    {
        return $this->belongsToMany(MenuItem::class, 'offer_menu_item')->withTimestamps();
    }

    /**
     * فحص ما إذا كان العرض سارياً وصالحاً في اللحظة الراهنة
     */
    public function isValidNow(): bool
    {
        if (! $this->status) {
            return false;
        }

        $now = Carbon::now();
        $todayDate = $now->toDateString();
        $currentTime = $now->format('H:i:s');
        $currentDay = $now->format('D'); // Mon, Tue, etc.

        // فحص التواريخ
        if ($todayDate < $this->start_date->toDateString() || $todayDate > $this->end_date->toDateString()) {
            return false;
        }

        // فحص أيام الأسبوع
        if (! empty($this->applicable_days) && ! in_array($currentDay, $this->applicable_days)) {
            return false;
        }

        // فحص الأوقات (إن حُددت)
        if ($this->start_time && $this->end_time) {
            if ($currentTime < $this->start_time || $currentTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }

    /**
     * حساب السعر بعد تطبيق الخصم على سعر معين
     */
    public function calculateDiscountedPrice(float $originalPrice): float
    {
        if ($this->discount_type === 'percentage') {
            $discount = ($originalPrice * ($this->discount_value / 100));
            return max(0, round($originalPrice - $discount, 2));
        }

        return max(0, round($originalPrice - $this->discount_value, 2));
    }
}
