<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\UserRegisterRequest;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(UserRegisterRequest $request)
    {
        $validatedData = $request->validated();

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        $token = auth('api')->login($user);
        return $this->respondWithToken($token);
    }

    public function login()
    {
        try {
            $credentials = request(['email', 'password']);

            if (! $token = auth('api')->attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciais inválidas.',
                    'data' => null
                ], 401);
            }

            $user = auth('api')->user();

            return response()->json([
                'success' => true,
                'message' => 'Login efetuado com sucesso.',
                'data' => [
                    'access_token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => auth('api')->factory()->getTTL() * 60,
                    'user' => $user
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao tentar fazer login. Por favor, tente novamente.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function me()
    {
        return response()->json(auth('api')->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function validateToken(Request $request)
    {
        try {
            $request->validate([
                'access_token' => 'required|string'
            ]);

            $token = $request->input('access_token');

            if (! $user = JWTAuth::setToken($token)->getPayload()->get('sub')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Token inválido ou expirado.',
                    'data' => null
                ], 401);
            }

            $user = JWTAuth::setToken($token)->authenticate();

            return response()->json([
                'success' => true,
                'message' => 'Token válido.',
                'data' => [
                    'access_token' => $token,
                    'user' => $user
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao validar o token.',
                'data' => $e->getMessage()
            ], 500);
        }
    }

    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
    }
}
