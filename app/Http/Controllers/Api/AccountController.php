<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Account\StoreAccountRequest;
use App\Http\Requests\Account\UpdateAccountRequest;
use App\Http\Resources\AccountResource;
use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * Display a listing of accounts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Account::query();

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by subtype
        if ($request->has('subtype')) {
            $query->where('subtype', $request->subtype);
        }

        // Filter active only
        if ($request->boolean('active_only')) {
            $query->active();
        }

        $accounts = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'accounts' => AccountResource::collection($accounts),
        ]);
    }

    /**
     * Store a newly created account
     */
    public function store(StoreAccountRequest $request): JsonResponse
    {
        $account = Account::create($request->validated());

        return response()->json([
            'message' => 'Account created successfully',
            'account' => new AccountResource($account),
        ], 201);
    }

    /**
     * Display the specified account
     */
    public function show(Account $account): JsonResponse
    {
        return response()->json([
            'account' => new AccountResource($account),
        ]);
    }

    /**
     * Update the specified account
     */
    public function update(UpdateAccountRequest $request, Account $account): JsonResponse
    {
        $account->update($request->validated());

        return response()->json([
            'message' => 'Account updated successfully',
            'account' => new AccountResource($account),
        ]);
    }

    /**
     * Remove the specified account
     */
    public function destroy(Account $account): JsonResponse
    {
        // Check if account has transactions
        if ($account->transactions()->count() > 0) {
            return response()->json([
                'message' => 'Cannot delete account with existing transactions. Please deactivate instead.',
            ], 422);
        }

        $account->delete();

        return response()->json([
            'message' => 'Account deleted successfully',
        ]);
    }

    /**
     * Get account summary
     */
    public function summary(): JsonResponse
    {
        $user = auth()->user();

        return response()->json([
            'summary' => [
                'total_assets' => $user->getTotalAssets(),
                'total_liabilities' => $user->getTotalLiabilities(),
                'net_worth' => $user->getNetWorth(),
                'accounts_count' => Account::count(),
                'active_accounts_count' => Account::active()->count(),
            ],
        ]);
    }
}
