<?php

namespace App\Http\Controllers;

use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuDisplayController extends Controller
{
    public function index(Request $request): View
    {
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
