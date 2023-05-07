<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentMethodController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $paymentMethods = DB::table('payment_methods')->get();
        return response()->json($paymentMethods, JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                "name"=> "required|string",
                "named_id"=> "required|integer"
            ]);
        } catch (ValidationException $e) {
            return response()->json($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $paymentMethod = PaymentMethod::create([
            "name"=> $request->name,
            "named_id"=> $request->named_id
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return response()->json("Payment Method Not Found", JsonResponse::HTTP_NOT_FOUND);
        } else {
            return response()->json($paymentMethod, JsonResponse::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $paymentMethod = PaymentMethod::find($id);
        if (!$paymentMethod) {
            return response()->json("Payment Method Not Found", JsonResponse::HTTP_NOT_FOUND);
        }
        try {
            $this->validate($request, [
                "name"=> "string",
                "named_id"=> "integer"
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        $paymentMethod->fill($request->all());
        $paymentMethod->save();
        return response()->json(null, JsonResponse::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $paymentMethod = PaymentMethod::find($id);
        $paymentMethod->delete();
    }
}
