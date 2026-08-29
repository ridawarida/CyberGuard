<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BehaviorCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    // List all categories
    public function index()
    {
        $categories = BehaviorCategory::orderBy('name')->get();
        return view('admin.categories.index', ['categories' => $categories]);
    }

    // Show create form
    public function create()
    {
        return view('admin.categories.create');
    }

    // Save new category
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:behavior_categories,name',
            'description' => 'nullable|string|max:500',
        ]);

        BehaviorCategory::create($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category added successfully.');
    }

    // Show edit form
    public function edit(BehaviorCategory $category)
    {
        return view('admin.categories.edit', ['category' => $category]);
    }

    // Update category
    public function update(Request $request, BehaviorCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:behavior_categories,name,' . $category->id,
            'description' => 'nullable|string|max:500',
        ]);

        $category->update($validated);

        return redirect()->route('admin.categories.index')->with('success', 'Category updated successfully.');
    }

    // Delete category
    public function destroy(BehaviorCategory $category)
    {
        $category->delete();
        return redirect()->route('admin.categories.index')->with('success', 'Category deleted successfully.');
    }
}