<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Payment;
use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();
        $account_id = $user->account_id;
        $payments = Payment::where('account_id', $account_id)->get();
        if (isset($payments)) {
            return response()->json($payments, JsonResponse::HTTP_OK);
        } else {
            return response()->json('Payment Not Found', JsonResponse::HTTP_NOT_FOUND);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $account_id = $user->account_id;

        try {
            $this->validate($request, [
                //payment
                'created_by'=> 'required|string|max:100',
                'amount'=> 'required|integer|min:1',
                'paid_at'=> 'date_format:Y/m/d|required',
                'check_no'=> 'nullable|integer|digits:10',
                'bank_name'=> 'nullable|string|max:20',
                //payment method
                // 'name'=> 'unique:payment_methods, name',
                // 'named_id'=> 'unique:payment_methods, named_id|integer'
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }
        
        $paymentMethod = PaymentMethod::create([
            'name'=> $request->name,
            'named_id'=> $request->named_id
        ]);
        $payment = Payment::create([
            'account_id'=> $account_id,
            'invoice_id'=> $request->invoice_id,
            'created_by'=> $request->created_by,
            'amount'=> $request->amount,
            'paid_at'=> $request->paid_at,
            'payment_method_id'=> $paymentMethod->id,
            'check_no'=> $request->check_no,
            'bank_name'=> $request->bank_name
        ]);
        return response()->json("Payment Created Successfully", JsonResponse::HTTP_CREATED);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json('Payment or Payment method not found', JsonResponse::HTTP_NOT_FOUND);
        } else {
            return response()->json($payment, JsonResponse::HTTP_OK);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $payment = Payment::find($id)->first();

        if(!$payment || $payment->account_id != $user->account_id) {
            return response()->json("Payment Not Found", JsonResponse::HTTP_NOT_FOUND);
        }

        try {
            $this->validate($request, [
                //payment
                'created_by'=> 'required|string|max:100',
                'amount'=> 'required|integer|min:1',
                'paid_at'=> 'date_format:m/d/Y|required',
                'check_no'=> 'nullable|integer|digits:10',
                'bank_name'=> 'nullable|string|max:20',
                //payment method
                'name'=> 'payment_methods, name',
                'named_id'=> 'payment_methods, named_id'
            ]);
        } catch (ValidationException $e) {
            return response()->json($e, JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        try {
            $data = $request->only(['created_at', 'amount', 'paid_at', 'check_no', 'bank_name']);
            $payment->fill($data)->save();
        } catch (Exception $e) {
            return response()->json($e, JsonResponse::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $payment = Payment::where('id', $id)->first();
        if (!$payment || $payment->account_id != $accountId) {
            return response()->json('This payment is not found !', JsonResponse::HTTP_NOT_FOUND);
        } else {
            $payment->delete();
            return response()->json(null, JsonResponse::HTTP_NO_CONTENT);
        }
    }
}
