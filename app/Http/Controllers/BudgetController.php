<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class BudgetController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $currentMonth = Carbon::now()->month;
        $currentYear = Carbon::now()->year;

        $budgets = Budget::where('user_id', $user->id)
            ->where('month', $currentMonth)
            ->where('year', $currentYear)
            ->with('category')
            ->get();

        $categories = Category::where('user_id', $user->id)
            ->where('type', Category::TYPE_EXPENSE)
            ->where('is_active', true)
            ->get();

        return view('budgets.index', compact('budgets', 'categories', 'currentMonth', 'currentYear'));
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'category_id' => [
                    'required',
                    'exists:categories,id',
                    function ($attribute, $value, $fail) {
                        $category = Category::find($value);
                        if ($category && $category->user_id !== auth()->id()) {
                            $fail('The selected category is invalid.');
                        }
                    },
                ],
                'amount' => 'required|numeric|min:0.01',
                'month' => 'required|integer|min:1|max:12',
                'year' => 'required|integer|min:2020',
                'rollover_enabled' => 'boolean',
            ]);

            $budget = Budget::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'category_id' => $validated['category_id'],
                    'month' => $validated['month'],
                    'year' => $validated['year'],
                ],
                [
                    'amount' => $validated['amount'],
                    'rollover_enabled' => $request->boolean('rollover_enabled'),
                ]
            );

            $budget->load('category');

            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Budget berhasil dibuat.',
                    'budget' => $budget
                ]);
            }

            return redirect()->route('budgets.index')->with('success', 'Budget created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $e->errors()
                ], 422);
            }
            throw $e;
        } catch (\Exception $e) {
            // Return JSON response for AJAX requests
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal membuat budget: ' . $e->getMessage()
                ], 500);
            }
            return back()->withErrors(['error' => 'Failed to create budget: ' . $e->getMessage()])->withInput();
        }
    }

    public function update(Request $request, $id)
    {
        $budget = Budget::where('user_id', auth()->id())->findOrFail($id);

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'rollover_enabled' => 'boolean',
        ]);

        $budget->update($validated);

        return redirect()->route('budgets.index')->with('success', 'Budget updated successfully.');
    }

    public function destroy($id)
    {
        $budget = Budget::where('user_id', auth()->id())->findOrFail($id);
        $budget->delete();

        return redirect()->route('budgets.index')->with('success', 'Budget deleted successfully.');
    }

    public function processRollover()
    {
        $user = auth()->user();
        $lastMonth = Carbon::now()->subMonth();
        
        $lastMonthBudgets = Budget::where('user_id', $user->id)
            ->where('month', $lastMonth->month)
            ->where('year', $lastMonth->year)
            ->where('rollover_enabled', true)
            ->get();

        foreach ($lastMonthBudgets as $budget) {
            $remaining = $budget->remaining;
            
            if ($remaining > 0) {
                $currentBudget = Budget::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'category_id' => $budget->category_id,
                        'month' => Carbon::now()->month,
                        'year' => Carbon::now()->year,
                    ],
                    [
                        'amount' => 0,
                        'rollover_enabled' => false,
                    ]
                );

                $currentBudget->rollover_amount = $remaining;
                $currentBudget->save();
            }
        }

        return redirect()->route('budgets.index')->with('success', 'Rollover processed successfully.');
    }
}


