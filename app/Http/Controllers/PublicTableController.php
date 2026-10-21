<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Table;
use Illuminate\View\View;

class PublicTableController extends Controller
{
    /**
     * Public page opened by scanning a table QR code.
     */
    public function show(string $table_number, string $token): View
    {
        $table = Table::where('table_number', $table_number)
            ->where('unique_token', $token)
            ->firstOrFail();

        $sections = Section::where('status', true)
            ->orderBy('display_order')
            ->with(['categories.subcategories.items' => function ($q) {
                $q->where('availability', 'available');
            }])
            ->get();

        return view('public.table_menu', compact('table', 'sections'));
    }
}
