<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $user = User::where(['email' => $request->email])->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'errors' => [
                    'The provided credentials are incorrect',
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        return response()->json([
            'success' => true,
            'token' =>  $user->createToken(config('app.name'))->plainTextToken,
        ], Response::HTTP_OK);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([], Response::HTTP_OK);
    }
}
