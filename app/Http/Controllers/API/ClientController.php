<?php

namespace App\Http\Controllers\API;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pageSizeParam = $request['page-size'];
        $keywordParam = $request['keyword'];

        $pageSize = is_numeric($pageSizeParam) ? (intval($pageSizeParam) > 0 ? intval($pageSizeParam) : 1) : 1;

        $account_id = Auth::user()->account_id;
        $query = Client::where('account_id', $account_id);
        if (isset($keywordParam)) {
            $query->where(function ($q) use ($keywordParam) {
                $q->where('name', 'LIKE', "%$keywordParam%")
                    ->orWhere('ice', 'LIKE', "%$keywordParam%");
            });
        }
        $clients = $query->orderBy('created_at', 'desc')->paginate($pageSize);

        return response()->json($clients, JsonResponse::HTTP_OK);
    }

    /**
     * Store a newly created resource in storage.
     */


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'ice' => 'required|string|digits:15',
            'if_no' => 'nullable|string|max:50',
            'rc_no' => 'nullable|string|max:180|min:2',
            'cnss_no' => 'nullable|string|max:50|min:10',
            'address' => 'nullable|string|max:50|min:10',
            'phone_number' => 'nullable|string|max:15'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $account_id = Auth::user()->account_id;
        $client = Client::create([
            "account_id" => $account_id,
            "name" => $request->name,
            "ice" => $request->ice,
            "if_no" => $request->if_no,
            "rc_no" => $request->rc_no,
            "cnss_no" => $request->cnss_no,
            "address" => $request->address,
            "phone_number" => $request->phone_number
        ]);

        return response()->json($client, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $accountId = Auth::user()->account_id;
        $client = Client::where('id', $id)->first();

        if (!$client || $client->account_id !== $accountId) {
            return response()->json([
                'message' => 'This client Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        }
        return response()->json($client, 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $accountId = Auth::user()->account_id;
        $client = Client::where('id', $id)->first();
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'ice' => 'required|string|digits:15',
            'if_no' => 'nullable|string|max:50',
            'rc_no' => 'nullable|string|max:180|min:2',
            'cnss_no' => 'nullable|string|max:50|min:10',
            'address' => 'nullable|string|max:50|min:10',
            'phone_number' => 'nullable|string|'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        if (!$client || $client->account_id !== $accountId) {
            return response()->json([
                'message' => 'This client Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } else {
            $client->name = $request->name;
            $client->ice = $request->ice;
            $client->if_no = $request->if_no;
            $client->rc_no = $request->rc_no;
            $client->cnss_no = $request->cnss_no;
            $client->address = $request->address;
            $client->phone_number = $request->phone_number;
            $client->save();
            return response()->json($client, JsonResponse::HTTP_OK);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $accountId = Auth::user()->account_id;
        $client = Client::where('id', $id)->first();

        if (!$client || $client->account_id !== $accountId) {
            return response()->json([
                'message' => 'This client Not Found'
            ], JsonResponse::HTTP_NOT_FOUND);
        } else {
            $client->delete();
            return response()->json([], JsonResponse::HTTP_NO_CONTENT);
        }
    }

}
