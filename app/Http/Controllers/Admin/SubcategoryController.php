<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SubcategoryRequest;
use App\Models\Category;
use App\Models\Section;
use App\Models\Subcategory;
use App\Services\MenuService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubcategoryController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request): View
    {
        $sections = Section::orderBy('display_order')->get();
        $categories = Category::orderBy('display_order')->get();

        $subcategories = Subcategory::with(['category.section'])
            ->withCount('items')
            ->when($request->filled('section_id'), function ($query) use ($request) {
                return $query->whereHas('category', function ($q) use ($request) {
                    $q->where('section_id', $request->section_id);
                });
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                return $query->where('category_id', $request->category_id);
            })
            ->orderBy('display_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.subcategories.index', compact('subcategories', 'sections', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::with('section')->where('status', true)->orderBy('display_order')->get();
        return view('admin.subcategories.create', compact('categories'));
    }

    public function store(SubcategoryRequest $request): RedirectResponse
    {
        $this->menuService->createSubcategory($request->validated());

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory created successfully.');
    }

    public function edit(Subcategory $subcategory): View
    {
        $categories = Category::with('section')->where('status', true)->orderBy('display_order')->get();
        return view('admin.subcategories.edit', compact('subcategory', 'categories'));
    }

    public function update(SubcategoryRequest $request, Subcategory $subcategory): RedirectResponse
    {
        $this->menuService->updateSubcategory($subcategory, $request->validated());

        return redirect()->route('admin.subcategories.index')
            ->with('success', 'Subcategory updated successfully.');
    }

    public function destroy(Subcategory $subcategory): RedirectResponse
    {
        try {
            $this->menuService->deleteSubcategory($subcategory);
            return redirect()->route('admin.subcategories.index')
                ->with('success', 'Subcategory deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('admin.subcategories.index')
                ->with('error', $e->getMessage());
        }
    }
}
