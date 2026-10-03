<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Section extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'display_order', 'status'];

    protected $casts = [
        'status' => 'boolean',
        'display_order' => 'integer',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->orderBy('display_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }
}