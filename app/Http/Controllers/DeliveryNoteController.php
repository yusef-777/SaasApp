<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class DeliveryNoteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSizeParam = $request['page-size'];
        $pageSize = is_numeric($pageSizeParam) ? (intval($pageSizeParam) > 0 ? intval($pageSizeParam) : 1) : 1;
        $accountId = Auth::user()->account_id;
        $deliveryNotes = DeliveryNote::where('account_id', $accountId)->paginate($pageSize);
        foreach ($deliveryNotes as $deliveryNote) {
            $client = Client::find($deliveryNote->client_id);
            $items = DeliveryNoteItem::where('delivery_note_id', $deliveryNote->id)->get();
            $deliveryNote->client = $client;
            $deliveryNote->items = $items;
        }
        return response()->json($deliveryNotes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = $this->myValidate($request, $user);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $deliveryNote = DeliveryNote::create([
            'account_id' => $user->account_id,
            'invoice_id' => $request->invoice_id,
            'estimate_id' => $request->estimate_id,
            'no' => $request->no,
            'issued_at' => date_create_from_format('d/m/Y', $request->issued_at),
            'status' => $request->status
        ]);

        foreach ($request->items as $item) {
            DeliveryNoteItem::create([
                'delivery_note_id' => $deliveryNote->id,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'quantity_unit' => $item['quantity_unit'],
            ]);
        }

        return response()->json(array_merge($deliveryNote->toArray(), [
            "items" => DeliveryNote::find($deliveryNote->id)->items,
        ]), 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accountId = Auth::user()->account_id;
        $deliveryNote = DeliveryNote::find($id);
        if (!$deliveryNote) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        } elseif ($deliveryNote->account_id !== $accountId) {
            return response()->json(null, JsonResponse::HTTP_FORBIDDEN);
        }
        return response()->json(array_merge($deliveryNote->toArray(), [
            "items" => DeliveryNote::find($id)->items,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $deliveryNote = DeliveryNote::where('id', $id)->first();
        $validator = $this->myValidate($request, $user, $deliveryNote);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }
        $deliveryNote->update(
            [
                'account_id' => $user->account_id,
                'invoice_id' => $request->invoice_id,
                'estimate_id' => $request->estimate_id,
                'no' => $request->no,
                'issued_at' => date_create_from_format('d/m/Y', $request->issued_at),
                'status' => $request->status
            ]
        );
        $undeletableItems = array_map(fn ($item) => $item['id'] ?? null, $request->items);
        $undeletableItems = array_filter($undeletableItems, fn ($item) => $item !== null);

        DeliveryNoteItem::where('delivery_note_id', $deliveryNote->id)
            ->whereNotIn('id', $undeletableItems)
            ->delete();

        foreach ($request->items as $item) {
            DeliveryNoteItem::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'delivery_note_id' => $deliveryNote->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'quantity_unit' => $item['quantity_unit'],
                ]
            );
        }

        return response()->json(array_merge($deliveryNote->toArray(), [
            "items" => DeliveryNote::find($deliveryNote->id)->items
        ]), 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $deliveryNote = DeliveryNote::where('id', $id)->first();
        if (!$deliveryNote || $deliveryNote->account_id !== $accountId) {
            return response()->json([
                'message' => 'This deliveryNote Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } else {
            $deliveryNote->delete();
            return response()->json([], JsonResponse::HTTP_NO_CONTENT);
        }
    }
    public function myValidate(Request $request, User $user, ?DeliveryNote $deliveryNote = null): ValidationValidator
    {
        $uniqueRule = Rule::unique('delivery_notes', 'no')->where(function ($query) use ($user) {
            return $query->where('account_id', $user->account_id);
        });

        if ($deliveryNote !== null) {
            $uniqueRule->ignore($deliveryNote->id);
        }

        return Validator::make($request->all(), [

            'no' => ['string', $uniqueRule],
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($user) {
                    return $query->where('account_id', $user->account_id);
                }),
            ],
            'invoice_id' => [
                'integer',
                Rule::exists('invoices', 'id')->where(function ($query) use ($user) {
                    return $query->where('account_id', $user->account_id);
                })
            ],
            'estimate_id' => [
                'integer',
                Rule::exists('estimates', 'id')->where(function ($query) use ($user) {
                    return $query->where('account_id', $user->account_id);
                })
            ],
            'issued_at' => 'required|date_format:d/m/Y',
            'status' => 'required|string|max:15|min:5',

            'items' => 'required|array',
            'items.*.id' => 'integer',
            'items.*.description' => 'required|string',
            'items.*.quantity_unit' => 'required|string',
            'items.*.quantity' => 'required|integer',
        ]);
    }
}
