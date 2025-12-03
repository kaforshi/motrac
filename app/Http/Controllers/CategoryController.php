<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::where('user_id', auth()->id())
            ->with('parent')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        // Get parent categories for each type
        $parentCategories = Category::where('user_id', auth()->id())
            ->whereNull('parent_id')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return view('categories.index', compact('categories', 'parentCategories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
        ]);

        // Validate that parent category has the same type
        if ($validated['parent_id']) {
            $parent = Category::where('user_id', auth()->id())
                ->where('id', $validated['parent_id'])
                ->where('type', $validated['type'])
                ->first();
            
            if (!$parent) {
                return redirect()->route('categories.index')
                    ->withErrors(['parent_id' => 'Parent category must be of the same type.'])
                    ->withInput();
            }
        }

        Category::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:income,expense,transfer',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|string|max:50',
            'color' => 'nullable|string|max:7',
            'is_active' => 'boolean',
        ]);

        // Validate that parent category has the same type and is not the category itself
        if ($validated['parent_id']) {
            if ($validated['parent_id'] == $id) {
                return redirect()->route('categories.index')
                    ->withErrors(['parent_id' => 'Category cannot be its own parent.'])
                    ->withInput();
            }
            
            $parent = Category::where('user_id', auth()->id())
                ->where('id', $validated['parent_id'])
                ->where('type', $validated['type'])
                ->first();
            
            if (!$parent) {
                return redirect()->route('categories.index')
                    ->withErrors(['parent_id' => 'Parent category must be of the same type.'])
                    ->withInput();
            }
        }

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
    }

    public function destroy($id)
    {
        $category = Category::where('user_id', auth()->id())->findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}

