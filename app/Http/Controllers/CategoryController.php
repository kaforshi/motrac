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

    public function show($id)
    {
        $category = Category::where('user_id', auth()->id())
            ->with(['parent', 'children'])
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'category' => $category
        ]);
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:income,expense,transfer',
                'parent_id' => 'nullable|exists:categories,id',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }

        // Validate that parent category has the same type
        $parentId = $validated['parent_id'] ?? null;
        if (!empty($parentId)) {
            $parent = Category::where('user_id', auth()->id())
                ->where('id', $parentId)
                ->where('type', $validated['type'])
                ->first();
            
            if (!$parent) {
                $error = ['parent_id' => ['Parent category must be of the same type.']];

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $error,
                    ], 422);
                }

                return redirect()->route('categories.index')
                    ->withErrors($error)
                    ->withInput();
            }
        }

        $category = Category::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $parentId,
            'icon' => $validated['icon'] ?? null,
            'color' => $validated['color'] ?? null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category created successfully.',
                'category' => $category,
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category created successfully.');
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('user_id', auth()->id())->findOrFail($id);

        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:income,expense,transfer',
                'parent_id' => 'nullable|exists:categories,id',
                'icon' => 'nullable|string|max:50',
                'color' => 'nullable|string|max:7',
                'is_active' => 'boolean',
            ]);

            // Validate that parent category has the same type and is not the category itself
            $parentId = $validated['parent_id'] ?? null;
            if ($parentId) {
                if ($parentId == $id) {
                    $error = ['parent_id' => ['Category cannot be its own parent.']];
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors' => $error,
                        ], 422);
                    }
                    return redirect()->route('categories.index')
                        ->withErrors($error)
                        ->withInput();
                }
                
                $parent = Category::where('user_id', auth()->id())
                    ->where('id', $parentId)
                    ->where('type', $validated['type'])
                    ->first();
                
                if (!$parent) {
                    $error = ['parent_id' => ['Parent category must be of the same type.']];
                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Validation failed',
                            'errors' => $error,
                        ], 422);
                    }
                    return redirect()->route('categories.index')
                        ->withErrors($error)
                        ->withInput();
                }
            }

            // Ensure parent_id is set to null if not provided
            $updateData = $validated;
            $updateData['parent_id'] = $parentId;
            
            $category->update($updateData);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Category updated successfully.',
                    'category' => $category,
                ]);
            }

            return redirect()->route('categories.index')->with('success', 'Category updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors(),
                ], 422);
            }
            throw $e;
        }
    }

    public function destroy(Request $request, $id)
    {
        $category = Category::where('user_id', auth()->id())->findOrFail($id);
        $category->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
            ]);
        }

        return redirect()->route('categories.index')->with('success', 'Category deleted successfully.');
    }
}

