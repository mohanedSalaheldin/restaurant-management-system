<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\MenuItemResource;
use App\Models\MenuItem;
use App\Models\Section;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MenuApiController extends Controller
{
    public function fullMenu(): JsonResponse
    {
        $sections = Section::where('status', true)
            ->orderBy('display_order')
            ->with([
                'categories' => fn ($q) => $q->where('status', true)->orderBy('display_order'),
                'categories.subcategories' => fn ($q) => $q->where('status', true)->orderBy('display_order'),
                'categories.subcategories.items' => fn ($q) => $q->where('availability', 'available')->with('offers'),
            ])
            ->get();

        // Items go through the resource so clients get final prices and active offers.
        $data = $sections->map(fn ($section) => [
            'id'          => $section->id,
            'name'        => $section->name,
            'description' => $section->description,
            'categories'  => $section->categories->map(fn ($category) => [
                'id'            => $category->id,
                'name'          => $category->name,
                'description'   => $category->description,
                'subcategories' => $category->subcategories->map(fn ($subcategory) => [
                    'id'          => $subcategory->id,
                    'name'        => $subcategory->name,
                    'description' => $subcategory->description,
                    'items'       => MenuItemResource::collection($subcategory->items),
                ]),
            ]),
        ]);

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }

    public function items(Request $request): JsonResponse
    {
        $request->validate([
            'category_id' => 'nullable|integer',
            'search'      => 'nullable|string|max:100',
        ]);

        $items = MenuItem::with('offers')
            ->where('availability', 'available')
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('search'), fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->paginate(15);

        return response()->json([
            'status'     => true,
            'data'       => MenuItemResource::collection($items),
            'pagination' => [
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
                'total'        => $items->total(),
            ],
        ]);
    }
}
