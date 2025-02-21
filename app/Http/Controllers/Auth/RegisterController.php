<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use function Illuminate\Support\defer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
 
class RegisterController extends Controller
{
    /**
     * Register a user
     * @method POST api/register
     * 
     * @param \App\Http\Requests\Auth\RegisterRequest $request
     * @return \App\Http\Resources\UserResource
     */
    public function register(RegisterRequest $request)
    {
       $validated =  $request->validated();

       $user = User::create($validated);

       defer(function()use($user, $validated){
            event(new Registered($user));
        });

    
       $token = auth('api')->login($user);

        return $this->respondWithToken($token);
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
            'user' => auth('api')->user(),
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'message' => 'Registered successfully'
        ]);
    }
}
