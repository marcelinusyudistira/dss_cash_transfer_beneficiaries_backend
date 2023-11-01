<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'  => 'required',
            'email'  => 'required|email:rfc,dns|unique:users',
            'password'  => 'required|min:8|confirmed',
            'alamat'  => 'required',
            'no_telpon'  => 'required|max:14'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }
 
        
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'alamat' => $request->alamat,
            'no_telpon' => $request->no_telpon,
            'is_admin' => 0,
            'password' => bcrypt($request->password),
        ]);
       
        return response()->json([
            'success' => true,
            'message' => 'Berhasil melakukan Registrasi',
            'user'    => $user  
        ]);
    }

    public function login(Request $request)
    {
        $loginData = $request->all();
        $validator = Validator::make($loginData, [
            'email' => 'required|email:rfc,dns',
            'password' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        if (!Auth::attempt($loginData)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Kata Sandi salah',
            ], 401);
        }

        $user = Auth::user();

        return response([
            'success' => true,
            'message' => 'Berhasil Login',
            'user' => $user, //penggunaan nama user dalam petik akan berpengaruh pada frontend untuk mengakses objek
            'token' => $user->createToken('Authentication Token')->accessToken    
        ]);
    }

    public function logout(Request $request){
        $token = $request->user()->token();
        $token->revoke();
        $response = ["message"=>"You have successfully logout!!"];
        return response($response,200);
    }
}
