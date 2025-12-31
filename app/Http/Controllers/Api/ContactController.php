<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\StoreContactRequest;
use App\Http\Requests\Contact\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    /**
     * Display a listing of contacts
     */
    public function index(Request $request): JsonResponse
    {
        $query = Contact::query();

        // Filter by balance status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'owes_you':
                    $query->owesYou();
                    break;
                case 'you_owe':
                    $query->youOwe();
                    break;
                case 'settled':
                    $query->settled();
                    break;
            }
        }

        // Filter active only
        if ($request->boolean('active_only')) {
            $query->active();
        }

        // Search by name
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $contacts = $query->orderBy('name')->get();

        return response()->json([
            'contacts' => ContactResource::collection($contacts),
        ]);
    }

    /**
     * Store a newly created contact
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        return response()->json([
            'message' => 'Contact created successfully',
            'contact' => new ContactResource($contact),
        ], 201);
    }

    /**
     * Display the specified contact
     */
    public function show(Contact $contact): JsonResponse
    {
        return response()->json([
            'contact' => new ContactResource($contact),
        ]);
    }

    /**
     * Update the specified contact
     */
    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->update($request->validated());

        return response()->json([
            'message' => 'Contact updated successfully',
            'contact' => new ContactResource($contact),
        ]);
    }

    /**
     * Remove the specified contact
     */
    public function destroy(Contact $contact): JsonResponse
    {
        // Check if contact has unsettled balance
        if (!$contact->isSettled()) {
            return response()->json([
                'message' => 'Cannot delete contact with unsettled balance. Current balance: ' . $contact->balance,
            ], 422);
        }

        $contact->delete();

        return response()->json([
            'message' => 'Contact deleted successfully',
        ]);
    }

    /**
     * Get debt summary
     */
    public function summary(): JsonResponse
    {
        $totalOwed = Contact::owesYou()->sum('balance');
        $totalOwing = abs(Contact::youOwe()->sum('balance'));

        return response()->json([
            'summary' => [
                'total_owed_to_you' => $totalOwed,
                'total_you_owe' => $totalOwing,
                'net_position' => $totalOwed - $totalOwing,
                'contacts_count' => Contact::count(),
                'settled_count' => Contact::settled()->count(),
            ],
        ]);
    }
}