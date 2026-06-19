<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Budget\StoreBudgetRequest;
use App\Http\Requests\Budget\UpdateBudgetRequest;
use App\Models\Budget;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function store(StoreBudgetRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['include_subcategories'] = $request->boolean('include_subcategories');

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

    public function update(UpdateBudgetRequest $request, Budget $budget): JsonResponse
    {
        $validated = $request->validated();

        if ($request->has('include_subcategories')) {
            $validated['include_subcategories'] = $request->boolean('include_subcategories');
        }

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
