<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use App\Models\Estimate;
use App\Models\EstimateItem;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Validator as ValidationValidator;


class EstimateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSizeParam = $request['page-size'];
        $pageSize = is_numeric($pageSizeParam) ? (intval($pageSizeParam) > 0 ? intval($pageSizeParam) : 1) : 1;
        $accountId = Auth::user()->account_id;
        $estimates = Estimate::where('account_id', $accountId)->paginate($pageSize);
        foreach ($estimates as $estimate) {
            $client = Client::find($estimate->client_id);
            $items = EstimateItem::where('estimate_id', $estimate->id)->get();
            $estimate->client = $client;
            $estimate->items = $items;
        }
        return response()->json($estimates);
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
        $estimate = new Estimate();
        $estimate->account_id = $user->account_id;
        $estimate->created_by = $user->id;
        $estimate->no = $request->no;
        $estimate->client_id = $request->client_id;
        $estimate->issued_at = date_create_from_format('d/m/Y', $request->issued_at);
        $estimate->vat = $request->vat;
        $estimate->save();

        $estimate_id = $estimate->id;

        foreach ($request->items as $item) {
            $estimateItem = new EstimateItem();
            $estimateItem->account_id = $user->account_id;
            $estimateItem->estimate_id = $estimate_id;
            $estimateItem->description = $item['description'];
            $estimateItem->unity_price = $item['unity_price'];
            $estimateItem->quantity = $item['quantity'];
            $estimateItem->quantity_unity = $item['quantity_unity'];
            $estimateItem->save();
        }

        $client = Client::find($estimate->client_id);
        $items = EstimateItem::where('estimate_id', $estimate_id)->get();
        return response()->json(array_merge($estimate->toArray(), [
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
        $estimate = Estimate::find($id);
        if (!$estimate) {
            return response()->json(null, JsonResponse::HTTP_NOT_FOUND);
        } elseif ($estimate->account_id !== $accountId) {
            return response()->json(null, JsonResponse::HTTP_FORBIDDEN);
        }
        return response()->json(array_merge($estimate->toArray(), [
            'client' => Estimate::find($id)->client,
            "items" => Estimate::find($id)->items,
        ]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $estimate = Estimate::where('id', $id)->first();
        $validator = $this->myValidate($request, $user, $estimate);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        $estimate->update(
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

        EstimateItem::where('estimate_id', $estimate->id)
            ->whereNotIn('id', $undeletableItems)
            ->delete();


        foreach ($request->items as $item) {
            EstimateItem::updateOrCreate(
                ['id' => $item['id'] ?? null],
                [
                    'account_id' => $user->account_id,
                    'estimate_id' => $estimate->id,
                    'description' => $item['description'],
                    'quantity_unity' => $item['quantity_unity'],
                    'quantity' => $item['quantity'],
                    'unity_price' => $item['unity_price']
                ]
            );
        }

        $updatedEstimate = Estimate::where('id', $estimate->id)->first();
        $updatedEstimateItems = EstimateItem::where('estimate_id', $estimate->id)->get();
        return response()->json([
            'estimate' => $updatedEstimate,
            'items' => $updatedEstimateItems
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $estimate = Estimate::where('id', $id)->first();
        if (!$estimate || $estimate->account_id !== $accountId) {
            return response()->json([
                'message' => 'This estimate Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } else {
            $estimate->delete();
            return response()->json([], JsonResponse::HTTP_NO_CONTENT);
        }
    }

    public function myValidate(Request $request, User $user, ?Estimate $estimate = null): ValidationValidator
    {
        $uniqueRule = Rule::unique('estimates', 'no')->where(function ($query) use ($user) {
            return $query->where('account_id', $user->account_id);
        });

        if ($estimate !== null) {
            $uniqueRule->ignore($estimate->id);
        }

        return Validator::make($request->all(), [

            'no' => ['string', $uniqueRule],
            'client_id' => [
                'required',
                Rule::exists('clients', 'id')->where(function ($query) use ($user) {
                    return $query->where('account_id', $user->account_id);
                }),
            ],
            'issued_at' => 'required|date_format:d/m/Y',
            'vat' => 'required|integer|max:50|min:10',

            'items' => 'required|array',
            'items.*.id' => 'integer',
            'items.*.description' => 'required|string',
            'items.*.quantity_unity' => 'required|string',
            'items.*.quantity' => 'required|integer',
            'items.*.unity_price' => 'required|numeric'

        ]);
    }
    public function nextNo()
    {
        $user = Auth::user();
        $lastNo = (Estimate::where('account_id', $user->account_id)->orderBy('created_at', 'desc')->select('no')->first())->no;

        $explodedNo = explode('/', $lastNo);
        $numberValue = count($explodedNo) === 2 ? $explodedNo[0] :  preg_replace("/[^0-9]/", "", $lastNo);;

        $nextNo = $lastNo;

        if (count($explodedNo) === 2) {
            $nextNo = ++$numberValue . '/' . $explodedNo[1];
        } else if (strlen(preg_replace("/[0-9]/", "", $lastNo))) {
            $nextNo = ++$numberValue . preg_replace("/[0-9]/", "", $lastNo);
        } else {
            $nextNo = ++$numberValue;
        }

        return response()->json($nextNo);
    }
}
// AB-111
// 11111
// 111/111