<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\MenuItemRequest;
use App\Models\Category;
use App\Models\MenuItem;
use App\Models\Section;
use App\Models\Subcategory;
use App\Services\MenuService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuItemController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request): View
    {
        $sections = Section::orderBy('display_order')->get();
        $categories = Category::orderBy('display_order')->get();
        $subcategories = Subcategory::orderBy('display_order')->get();

        $items = MenuItem::with(['section', 'category', 'subcategory'])
            ->when($request->filled('section_id'), fn($q) => $q->where('section_id', $request->section_id))
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('subcategory_id'), fn($q) => $q->where('subcategory_id', $request->subcategory_id))
            ->when($request->filled('availability'), fn($q) => $q->where('availability', $request->availability))
            ->when($request->filled('search'), fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.menu_items.index', compact('items', 'sections', 'categories', 'subcategories'));
    }

    public function create(): View
    {
        $sections = Section::where('status', true)->orderBy('display_order')->get();
        return view('admin.menu_items.create', compact('sections'));
    }

    public function store(MenuItemRequest $request): RedirectResponse
    {
        $this->menuService->createMenuItem(
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $sections = Section::where('status', true)->orderBy('display_order')->get();
        $categories = Category::where('section_id', $menuItem->section_id)->where('status', true)->get();
        $subcategories = Subcategory::where('category_id', $menuItem->category_id)->where('status', true)->get();

        return view('admin.menu_items.edit', compact('menuItem', 'sections', 'categories', 'subcategories'));
    }

    public function update(MenuItemRequest $request, MenuItem $menuItem): RedirectResponse
    {
        $this->menuService->updateMenuItem(
            $menuItem,
            $request->validated(),
            $request->file('image')
        );

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $this->menuService->deleteMenuItem($menuItem);

        return redirect()->route('admin.menu-items.index')
            ->with('success', 'Menu item deleted successfully.');
    }

    // دوال مساعدة لجلب البيانات للـ Cascading Dropdowns
    public function getCategoriesBySection(Section $section): JsonResponse
    {
        return response()->json($section->categories()->where('status', true)->get());
    }

    public function getSubcategoriesByCategory(Category $category): JsonResponse
    {
        return response()->json($category->subcategories()->where('status', true)->get());
    }
}
