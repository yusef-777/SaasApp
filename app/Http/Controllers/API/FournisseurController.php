<?php

namespace App\Http\Controllers\API;

use App\Models\Fournisseur;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Resources\Json\JsonResource;


class FournisseurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $account_id = Auth::user()->account_id;
        $allFournisseur = Fournisseur::where('account_id', $account_id)->get();
        return response()->json($allFournisseur ,JsonResponse::HTTP_OK);


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'address' => 'nullable|string|max:50',
            'phone_number' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:180|min:2',
            'ice_no' => 'nullable|string|digits:15',
            'rc_no' => 'nullable|string|max:50|min:10',
            'nss_no' => 'nullable|integer|max:50|min:10'
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account_id = Auth::user()->account_id;
        $fournisseur =  Fournisseur::create([
            "account_id" =>     $account_id, 
            "name" =>           $request->name , 
            "address" =>        $request->address , 
            "phone_number" =>   $request->phone_number , 
            "email" =>          $request->email , 
            "ice_no" =>         $request->ice_no , 
            "rc_no" =>          $request->rc_no , 
            "cnss_no"   =>      $request->cnss_no 
        ]);
        return response()->json($fournisseur, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accountId = Auth::user()->account_id;
        $fournisseur = Fournisseur::where('id', $id)->first();

        if (!$fournisseur || $fournisseur->account_id !== $accountId) {
            return response()->json([
                'message' => 'This fournisseur Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json($fournisseur, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accountId = Auth::user()->account_id;
        $fournisseur = Fournisseur::where('id', $id)->first();

        if (!$fournisseur || $fournisseur->account_id !== $accountId) {
            return response()->json([
                'message' => 'This fournisseur Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }else{            
            $fournisseur->name         = $request->name;
            $fournisseur->address      = $request->address;
            $fournisseur->phone_number = $request->phone_number;
            $fournisseur->email        = $request->email;
            $fournisseur->ice_no       = $request->ice_no;
            $fournisseur->rc_no        = $request->rc_no ;
            $fournisseur->cnss_no      = $request->cnss_no;
            $fournisseur->save();
            return response()->json($fournisseur, JsonResponse::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $fournisseur = Fournisseur::where('id', $id)->first();

        if (!$fournisseur || $fournisseur->account_id !== $accountId) {
            return response()->json([
                'message' => 'This Fournisseur Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }else{
            $fournisseur->delete();
            return response()->json([], JsonResponse::HTTP_NO_CONTENT);
        }
}
}
