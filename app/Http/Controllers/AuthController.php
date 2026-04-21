<?php

namespace App\Http\Controllers;

use App\ApiResponseTrait;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    use ApiResponseTrait;

    public function login(LoginRequest $request)
    {
        $credentials = $request->validated();

        Auth::attempt($credentials);

        $request->session()->regenerate();

        return $this->successResponse(
            auth()->user(),
            'Logged in successfully',
            Response::HTTP_OK
        );
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->successResponse(
            null,
            'Logged out successfully',
            Response::HTTP_NO_CONTENT
        );
    }

    public function register(RegisterRequest $request)
    {
        $validated = $request->validated();

        $user = \App\Models\User::create($validated);

        return $this->successResponse(
            $user,
            'User registered successfully',
            Response::HTTP_CREATED
        );
    }
}
