<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuDisplayController extends Controller
{
    /**
     * عرض قائمة المنيو التفاعلية بالكامل
     */
    public function index(Request $request): View
    {
        // جلب الأقسام النشطة مع تصنيفاتها وتصنيفاتها الفرعية وأصنافها المتاحة وعروضها
        $sections = Section::where('status', true)
            ->orderBy('display_order')
            ->with([
                'categories' => function ($q) {
                    $q->where('status', true)->orderBy('display_order');
                },
                'categories.subcategories' => function ($q) {
                    $q->where('status', true)->orderBy('display_order');
                },
                'categories.subcategories.items' => function ($q) {
                    $q->where('availability', 'available')->with('offers');
                }
            ])
            ->get();

        return view('menu.display', compact('sections'));
    }
}
