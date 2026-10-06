<?php

namespace App\Http\Controllers;

use App\Models\Section;
use App\Models\Table;
use Illuminate\View\View;

class PublicTableController extends Controller
{
    /**
     * الصفحة التي تفتح للزبون عند مسح كود الطاولة
     */
    public function show(string $table_number, string $token): View
    {
        // التحقق من صحة رقم الطاولة والرمز السري
        $table = Table::where('table_number', $table_number)
            ->where('unique_token', $token)
            ->firstOrFail();

        // جلب المنيو بالكامل للعرض فقط
        $sections = Section::where('status', true)
            ->orderBy('display_order')
            ->with(['categories.subcategories.items' => function ($q) {
                $q->where('availability', 'available');
            }])
            ->get();

        return view('public.table_menu', compact('table', 'sections'));
    }
}
