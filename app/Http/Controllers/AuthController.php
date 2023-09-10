<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use  App\Models\User;

class AuthController extends Controller{
    
    public function __construct(){
        $this->middleware('auth:api', ['except' => ['login', 'refresh','register']]);
    }

    /**
     * Register new users using JWT_SECRET
     */
    public function register(Request $request) {
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|unique:users|string',
            'password' => 'required|string',
            'secret' => 'required|string',
        ]);
    
        if ($request['secret']!=env('JWT_SECRET')) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    
        $user = User::create([
            'name' => $request['name'], 
            'email' => $request['email']
        ]);
    
        $user->password = Hash::make($request['password']);
        $user->save();
    
    
        return response()->json(['message' => 'User registered, please login'], 201);
    }
    

    /**
     * Get a JWT via given credentials.
     *
     * @param  Request  $request
     * @return Response
     */
    public function login(Request $request)
    {

        $this->validate($request, [
            'email' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        // return response()->json(Auth::attempt($credentials), 201);


        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }
}