<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use function Illuminate\Support\defer;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{

    /**
     * Login a user
     * @param App\Http\LoginRequest $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        if (! $token = auth('api')->attempt($credentials)) {

            return response()->json([
                'status' => 'failed',
                'message' => 'These credentials do not match our records.',
                'errors' => [
                    'credentials' => ['These credentials do not match our records.'],
                ]
            ], 401);
        }

        $remember_me = $validation['remember_me'] ?? false;

        defer(function()use($remember_me){
            event(new Login($guard='api', auth()->user(), $remember_me));
        });


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
            'message' => 'Login successfully'
        ]);
    }

    /**
     * Logout auth user (Invalidate the token).
     * @param  \Illuminate\Http\Request $request
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    {
        auth()->logout();

        event(new Logout($guard='api', $request->user));

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out successfully',
        ]);
    }

}
