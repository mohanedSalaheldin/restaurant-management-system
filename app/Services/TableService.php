<?php

namespace App\Services;

use App\Models\Table;
use Illuminate\Support\Str;

class TableService
{
    public function createTable(array $data): Table
    {
        return Table::create($data);
    }

    public function updateTable(Table $table, array $data): Table
    {
        $table->update($data);
        return $table;
    }

    public function deleteTable(Table $table): void
    {
        $table->delete();
    }

    /**
     * إعادة توليد رمز الـ QR للطاولة
     */
    public function regenerateQrToken(Table $table): Table
    {
        $table->update([
            'unique_token' => Str::random(32),
        ]);

        return $table;
    }
}
