<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        $authenticated = Auth::guard('web')->attempt($credentials);

        if (! $authenticated) {
            return $this->errorResponse(
                'Invalid credentials',
                [
                    'email' => ['The provided credentials are incorrect.'],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($request->hasSession()) {
            $request->session()->regenerate();
        }

        return $this->successResponse(
            Auth::guard('web')->user(),
            'Logged in successfully',
            Response::HTTP_OK
        );
    }

    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->successResponse(
            null,
            'Logged out successfully',
            Response::HTTP_OK
        );
    }

    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::create($validated);

        return $this->successResponse(
            $user,
            'User registered successfully',
            Response::HTTP_CREATED
        );
    }
}
