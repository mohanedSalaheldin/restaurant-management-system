<?php

namespace App\Services;

use App\Models\Section;
use App\Models\Category;
use App\Models\Subcategory;
use Exception;

class MenuService
{
    // ================= Sections =================

    public function createSection(array $data): Section
    {
        return Section::create($data);
    }

    public function updateSection(Section $section, array $data): Section
    {
        $section->update($data);
        return $section;
    }

    public function deleteSection(Section $section): void
    {
        // التحقق من شرط المشروع: منع حذف القسم إذا احتوى تصنيفات
        if ($section->categories()->exists()) {
            throw new Exception("Cannot delete section containing active categories.");
        }
        $section->delete();
    }

    // ================= Categories =================

    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }

    public function updateCategory(Category $category, array $data): Category
    {
        $category->update($data);
        return $category;
    }

    public function deleteCategory(Category $category): void
    {
        // التحقق من شرط المشروع: منع الحذف إذا احتوى تصنيفات فرعية أو أصناف
        if ($category->subcategories()->exists() || $category->items()->exists()) {
            throw new Exception("Cannot delete category containing subcategories or items.");
        }
        $category->delete();
    }


    // ================= Subcategories =================

    public function createSubcategory(array $data): Subcategory
    {
        return Subcategory::create($data);
    }

    public function updateSubcategory(Subcategory $subcategory, array $data): Subcategory
    {
        $subcategory->update($data);
        return $subcategory;
    }

    public function deleteSubcategory(Subcategory $subcategory): void
    {
        // التحقق من شرط المشروع: منع حذف التصنيف الفرعي إذا احتوى أصنافاً
        if ($subcategory->items()->exists()) {
            throw new Exception("Cannot delete subcategory containing active menu items.");
        }
        $subcategory->delete();
    }

    // ================= Menu Items =================

    public function createMenuItem(array $data, ?\Illuminate\Http\UploadedFile $image = null): \App\Models\MenuItem
    {
        if ($image) {
            $data['image'] = $image->store('menu_items', 'public');
        }

        return \App\Models\MenuItem::create($data);
    }

    public function updateMenuItem(\App\Models\MenuItem $item, array $data, ?\Illuminate\Http\UploadedFile $image = null): \App\Models\MenuItem
    {
        if ($image) {
            if ($item->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($item->image);
            }
            $data['image'] = $image->store('menu_items', 'public');
        }

        $item->update($data);
        return $item;
    }

    public function deleteMenuItem(\App\Models\MenuItem $item): void
    {
        if ($item->image && \Illuminate\Support\Facades\Storage::disk('public')->exists($item->image)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($item->image);
        }
        $item->delete();
    }
}
