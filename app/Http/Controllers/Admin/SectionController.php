<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SectionRequest;
use App\Models\Section;
use App\Services\MenuService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function __construct(
        protected MenuService $menuService
    ) {}

    public function index(): View
    {
        $sections = Section::withCount('categories')
            ->orderBy('display_order')
            ->paginate(15);

        return view('admin.sections.index', compact('sections'));
    }

    public function create(): View
    {
        return view('admin.sections.create');
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        $this->menuService->createSection($request->validated());

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section created successfully.');
    }

    public function edit(Section $section): View
    {
        return view('admin.sections.edit', compact('section'));
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $this->menuService->updateSection($section, $request->validated());

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section updated successfully.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        try {
            $this->menuService->deleteSection($section);
            return redirect()->route('admin.sections.index')
                ->with('success', 'Section deleted successfully.');
        } catch (Exception $e) {
            return redirect()->route('admin.sections.index')
                ->with('error', $e->getMessage());
        }
    }
}
