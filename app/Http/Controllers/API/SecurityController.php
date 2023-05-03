<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\Account;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Models\AccountSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class SecurityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);
        if(Auth::attempt($credentials)){ 
            $user = Auth::user(); 
            return response()->json([
                'message' => 'Login Success '.$user->email
            ],200);   
        } 
        else{ 
            return response()->json([
                'message' =>  'Email Or Password Incorrect'
            ]
            , 401);
        } 
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                // user
                'email' => 'required|string|max:180|unique:users',
                'password' => [
                    Password::min(8)
                        ->letters()
                        ->mixedCase()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()
                ],

                'role'=> 'string|max:10|in:'. implode(",", USER::ROLES),
                'status'=> 'string|max:10',
                'email_verified'=> 'boolean',
                'first_name' => 'required|string|max:50',
                'last_name' => 'required|string|max:50',
                'phone_number' => 'nullable|string|max:20',
                'client_id' => 'nullable|integer',
                // company
                'company_name' => 'required|string|max:50|min:3',
                'ice_no' => 'required|integer|digits:15',
                'address' => 'string|max:250',
                'city' => 'string|max:32',
            ]);
        } catch (ValidationException $e) {
            return response()->json($e->errors(), 422);
        }

        


        $account = new Account();
        $account->save();
        $id = $account->id;

        $accountsetting = new AccountSetting();
        $accountsetting->vat_rate  = 20;
        $accountsetting->account_id  = $id;
        $accountsetting->save();

        $user = new User();
        $user->account_id  = $id;
        $user->email  = $request->email;
        $user->password  = Hash::make($request->password);
        $user->role  = User::ROLE_ADMIN;
        $user->status  = 'active';
        $user->email_verified  = true;
        $user->first_name  = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->phone_number  = $request->phone_number;
        $user->save();
        $company = new Company();
        $company->account_id  = $id;
        $company->name  = $request->company_name;
        $company->ice_no  = $request->ice_no;

        if($company->save()){
            $success['token'] =  $user->createToken('MyApp')->plainTextToken;
            Auth::login($user);
            return response()->json([
                'message' => $success
            ],201);
        }
   
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
