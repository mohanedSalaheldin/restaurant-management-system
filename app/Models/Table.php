<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Table extends Model
{
    use HasFactory;

    protected $fillable = [
        'table_number',
        'type',
        'min_capacity',
        'max_capacity',
        'location',
        'status',
        'unique_token',
        'notes',
    ];

    protected $casts = [
        'min_capacity' => 'integer',
        'max_capacity' => 'integer',
    ];

    /**
     * توليد رمز مميز فريد لكل طاولة تلقائياً عند الإنشاء
     */
    protected static function booted(): void
    {
        static::creating(function ($table) {
            if (empty($table->unique_token)) {
                $table->unique_token = Str::random(32);
            }
        });
    }

    /**
     * الحصول على الرابط الخاص بالزبون عند مسح الـ QR
     */
    public function getQrUrlAttribute(): string
    {
        return route('table.qr.view', [
            'table_number' => $this->table_number,
            'token'        => $this->unique_token,
        ]);
    }
}