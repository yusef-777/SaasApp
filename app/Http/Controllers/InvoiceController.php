<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSizeParam = $request['page-size'];

        $pageSize = is_numeric($pageSizeParam) ? (intval($pageSizeParam) > 0 ? intval($pageSizeParam) : 1) : 1;

        $accountId = Auth::user()->account_id;
        $invoices = Invoice::where('account_id', $accountId)->paginate($pageSize);
        foreach ($invoices as $invoice) {
            $client = Client::find($invoice->client_id);
            $items = InvoiceItem::where('invoice_id', $invoice->id)->get();
            $invoice->client = $client;
            $invoice->items = $items;
        }
        return response()->json($invoices);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $validator = $this->myValidator($request, $user);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        $invoice = new Invoice();
        $invoice->account_id = $user->account_id;
        $invoice->created_by = $user->id;
        $invoice->no = $request->no;
        $invoice->client_id = $request->client_id;
        $invoice->issued_at = date_create_from_format('d/m/Y', $request->issued_at);
        $invoice->vat = $request->vat;
        $invoice->save();

        $invoiceId = $invoice->id;

        foreach ($request->items as $item) {
            $invoiceItem = new InvoiceItem();
            $invoiceItem->account_id = $user->account_id;
            $invoiceItem->invoice_id = $invoiceId;
            $invoiceItem->description = $item['description'];
            $invoiceItem->unity_price = $item['unity_price'];
            $invoiceItem->quantity = $item['quantity'];
            $invoiceItem->quantity_unity = $item['quantity_unity'];
            $invoiceItem->save();
        }
        $client = Client::find($invoice->client_id);
        $items = InvoiceItem::where('invoice_id', $invoiceId)->get();
        return response()->json(array_merge($invoice->toArray(), [
            'client' => $client,
            "items" => $items,
        ]));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accountId = Auth::user()->account_id;
        $invoice = Invoice::find($id);
        if (!$invoice) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        } elseif ($invoice->account_id !== $accountId) {
            return response()->json(null, JsonResponse::HTTP_FORBIDDEN);
        }
        $client = Client::find($invoice->client_id);
        $items = InvoiceItem::where('invoice_id', $id)->get();

        return response()->json(array_merge($invoice->toArray(), [
            'client' => Invoice::find($id)->client,
            "items" => Invoice::find($id)->items,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $invoice = Invoice::where('id', $id)->first();
        $validator = $this->myValidator($request, $user, $invoice);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $invoice->update(
            [
                'account_id' => $user->account_id,
                'created_by' => $request->created_by,
                'no' => $request->no,
                'client_id' => $request->client_id,
                'issued_at' => date_create_from_format('d/m/Y', $request->issued_at),
                'vat' => $request->vat
            ]
        );

        $undeletableItems = array_map(fn ($item) => $item['id'] ?? null, $request->items);
        $undeletableItems = array_filter($undeletableItems, fn ($item) => $item !== null);

        InvoiceItem::where('invoice_id', $invoice->id)
            ->whereNotIn('id', $undeletableItems)
            ->delete();


        foreach ($request->items as $item) {
            InvoiceItem::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'account_id' => $user->account_id,
                    'invoice_id' => $invoice->id,
                    'description' => $item['description'],
                    'quantity_unity' => $item['quantity_unity'],
                    'quantity' => $item['quantity'],
                    'unity_price' => $item['unity_price']
                ]
            );
        }

        $updatedInvoice = Invoice::where('id', $invoice->id)->first();
        $updatedInvoiceItems = InvoiceItem::where('invoice_id', $invoice->id)->get();
        return response()->json([
            'invoice' => $updatedInvoice,
            'items' => $updatedInvoiceItems
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $invoice = Invoice::where('id', $id)->first();
        if (!$invoice || $invoice->account_id !== $accountId) {
            return response()->json([
                'message' => 'This invoice Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } else {
            $invoice->delete();
            return response()->json([], JsonResponse::HTTP_NO_CONTENT);
        }
    }
    public function myValidator(Request $request, User $user, ?Invoice $invoice = null): ValidationValidator
    {
        $uniqueRule = Rule::unique('invoices', 'no')->where(function ($query) use ($user) {
            return $query->where('account_id', $user->account_id);
        });

        if ($invoice !== null) {
            $uniqueRule->ignore($invoice->id);
        }

        return Validator::make($request->all(), [
            // Invoice
            'no' => ['string', $uniqueRule],
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($user) {
                    return $query->where('account_id', $user->account_id);
                }),
            ],
            'issued_at' => 'required|date_format:d/m/Y',
            'vat' => 'required|integer|max:50|min:10',
            // InvoiceItem
            'items' => 'required|array',
            'items.*.id' => 'integer',
            'items.*.description' => 'required|string',
            'items.*.quantity_unity' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.unity_price' => 'required|numeric'

        ]);
    }
    public function toDeliveryNote($id)
    {

        $invoice = Invoice::find($id);
        $deliveryNote = new DeliveryNote;
        $deliveryNote->account_id = $invoice->account_id;
        $deliveryNote->invoice_id = $invoice->id;
        $deliveryNote->client_id = $invoice->client_id;
        $deliveryNote->no = $invoice->no;
        $deliveryNote->issued_at = $invoice->issued_at;
        $deliveryNote->save();
        foreach ($invoice->items as $invoiceItem) {
            $deliveryNoteItem = new DeliveryNoteItem;
            // $deliveryNoteItem->account_id = $invoice->account_id;

            $deliveryNoteItem->delivery_note_id = $deliveryNote->id;
            $deliveryNoteItem->description = $invoiceItem['description'];
            $deliveryNoteItem->quantity = $invoiceItem['quantity'];
            $deliveryNoteItem->quantity_unit = $invoiceItem['quantity_unit'];
            $deliveryNoteItem->save();
        }
        return response()->json(array_merge($invoice->toArray(), [
            'client' => Invoice::find($id)->client,
            "items" => Invoice::find($id)->items,
        ]), 201);
    }
}
