<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CategoryRequest;
use App\Models\Category;
use App\Models\Section;
use App\Services\MenuService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(Request $request): View
    {
        $sections = Section::orderBy('display_order')->get();

        $categories = Category::with('section')
            ->withCount(['subcategories', 'items'])
            ->when($request->filled('section_id'), function ($query) use ($request) {
                return $query->where('section_id', $request->section_id);
            })
            ->orderBy('display_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.categories.index', compact('categories', 'sections'));
    }

    public function create(): View
    {
        $sections = Section::where('status', true)->orderBy('display_order')->get();
        return view('admin.categories.create', compact('sections'));
    }

    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->menuService->createCategory($request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function edit(Category $category): View
    {
        $sections = Section::where('status', true)->orderBy('display_order')->get();
        return view('admin.categories.edit', compact('category', 'sections'));
    }

    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $this->menuService->updateCategory($category, $request->validated());

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        try {
            $this->menuService->deleteCategory($category);
            return redirect()->route('admin.categories.index')
                ->with('success', 'Category deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('admin.categories.index')
                ->with('error', $e->getMessage());
        }
    }
}