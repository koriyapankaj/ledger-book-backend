<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\StoreTransactionRequest;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Http\Resources\TransactionResource;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * Display a listing of transactions
     */
    public function index(Request $request): JsonResponse
    {
        $query = Transaction::with(['account', 'toAccount', 'category', 'contact']);

        // Filter by type
        if ($request->has('type')) {
            $query->ofType($request->type);
        }

        // Filter by account
        if ($request->has('account_id')) {
            $query->where('account_id', $request->account_id);
        }

        // Filter by category
        if ($request->has('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by contact
        if ($request->has('contact_id')) {
            $query->where('contact_id', $request->contact_id);
        }

        // Filter by date range
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->dateRange($request->start_date, $request->end_date);
        }

        // Filter by period
        if ($request->has('period')) {
            switch ($request->period) {
                case 'today':
                    $query->whereDate('transaction_date', today());
                    break;
                case 'week':
                    $query->whereBetween('transaction_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'month':
                    $query->thisMonth();
                    break;
                case 'year':
                    $query->thisYear();
                    break;
            }
        }

        // Search
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        $transactions = $query->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'transactions' => TransactionResource::collection($transactions),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'last_page' => $transactions->lastPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
            ],
        ]);
    }

    /**
     * Store a newly created transaction
     */
    public function store(StoreTransactionRequest $request): JsonResponse
    {
        $transaction = $this->transactionService->createTransaction($request->validated());

        return response()->json([
            'message' => 'Transaction created successfully',
            'transaction' => new TransactionResource($transaction),
        ], 201);
    }

    /**
     * Display the specified transaction
     */
    public function show(Transaction $transaction): JsonResponse
    {
        $transaction->load(['account', 'toAccount', 'category', 'contact']);

        return response()->json([
            'transaction' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Update the specified transaction
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction): JsonResponse
    {
        $transaction = $this->transactionService->updateTransaction($transaction, $request->validated());

        return response()->json([
            'message' => 'Transaction updated successfully',
            'transaction' => new TransactionResource($transaction),
        ]);
    }

    /**
     * Remove the specified transaction
     */
    public function destroy(Transaction $transaction): JsonResponse
    {
        $this->transactionService->deleteTransaction($transaction);

        return response()->json([
            'message' => 'Transaction deleted successfully',
        ]);
    }

    /**
     * Get transaction statistics
     */
    public function statistics(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $statistics = $this->transactionService->getStatistics($period);

        return response()->json([
            'statistics' => $statistics,
        ]);
    }

    /**
     * Get spending by category
     */
    public function spendingByCategory(Request $request): JsonResponse
    {
        $period = $request->get('period', 'month');
        $spending = $this->transactionService->getSpendingByCategory($period);

        return response()->json([
            'spending_by_category' => $spending,
        ]);
    }
}