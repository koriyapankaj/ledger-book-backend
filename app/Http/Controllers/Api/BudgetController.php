<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Budget::with('category');

        if ($request->boolean('active_only')) {
            $query->active();
        }

        if ($request->boolean('current_only')) {
            $query->current();
        }

        $budgets = $query->orderBy('created_at', 'desc')->get();

        // Calculate spent amounts for each budget
        $budgets->each(function ($budget) {
            $budget->spent_amount = $budget->getSpentAmount();
            $budget->remaining_amount = $budget->getRemainingAmount();
            $budget->percentage_used = $budget->getPercentageUsed();
            $budget->is_over_budget = $budget->isOverBudget();
        });

        return response()->json([
            'budgets' => $budgets,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'period' => ['required', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
        ]);

        $budget = Budget::create($validated);

        return response()->json([
            'message' => 'Budget created successfully',
            'budget' => $budget->load('category'),
        ], 201);
    }

    public function show(Budget $budget): JsonResponse
    {
        $budget->load('category');
        $budget->spent_amount = $budget->getSpentAmount();
        $budget->remaining_amount = $budget->getRemainingAmount();
        $budget->percentage_used = $budget->getPercentageUsed();
        $budget->is_over_budget = $budget->isOverBudget();

        return response()->json([
            'budget' => $budget,
        ]);
    }

    public function update(Request $request, Budget $budget): JsonResponse
    {
        $validated = $request->validate([
            'category_id' => ['sometimes', 'exists:categories,id'],
            'amount' => ['sometimes', 'numeric', 'min:0.01'],
            'period' => ['sometimes', Rule::in(['daily', 'weekly', 'monthly', 'yearly'])],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        $budget->update($validated);

        return response()->json([
            'message' => 'Budget updated successfully',
            'budget' => $budget->load('category'),
        ]);
    }

    public function destroy(Budget $budget): JsonResponse
    {
        $budget->delete();

        return response()->json([
            'message' => 'Budget deleted successfully',
        ]);
    }
}
