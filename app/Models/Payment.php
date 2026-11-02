<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_id',
        'user_id',
        'payment_method',
        'amount_tendered',
        'amount_paid',
        'change_returned',
        'reference_number',
    ];

    protected $casts = [
        'amount_tendered' => 'decimal:2',
        'amount_paid'     => 'decimal:2',
        'change_returned' => 'decimal:2',
    ];

    public function bill(): BelongsTo
    {
        return $this->belongsTo(Bill::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
