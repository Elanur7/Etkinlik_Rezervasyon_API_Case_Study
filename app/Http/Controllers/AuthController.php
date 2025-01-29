<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = JWTAuth::fromUser($user);

        // Session'a kullanıcı ve token bilgisini kaydet
        Session::put('user', $user);
        Session::put('token', $token);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ]);
    }

    public function login(Request $request)
    {
        // Gelen istek için doğrulama kuralları
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string'
        ]);

        // Kullanıcı bilgilerini alıyoruz
        $credentials = $request->only(['email', 'password']);

        // JWT token'ını almak için attempt kullanıyoruz
        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized'
            ], 401);
        }

        // Başarılı giriş sonrası kullanıcı bilgilerini alıyoruz
        $user = Auth::user();

        // Kullanıcı ve token bilgisini döndürüyoruz
        return response()->json([
            'status' => 'success',
            'user' => $user,
            'authorization' => [
                'token' => $token,
                'type' => 'bearer'
            ]
        ]);
    }

    public function refresh(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No token provided'
                ], 400);
            }

            // Token'ı ayarlıyoruz
            JWTAuth::setToken($token);

            // Token'ı yeniliyoruz
            $newToken = JWTAuth::refresh();

            return response()->json([
                'status' => 'success',
                'message' => 'Token refreshed successfully',
                'authorization' => [
                    'token' => $newToken,
                    'type' => 'bearer'
                ]
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token has expired. Please log in again.'
            ], 401);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh token'
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $token = $request->bearerToken();

            if (!$token || !JWTAuth::parseToken()->check()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invalid or expired token'
                ], 400);
            }

            JWTAuth::invalidate($token);

            Session::forget('user');
            Session::forget('token');

            return response()->json([
                'status' => 'success',
                'message' => 'User logged out successfully',
            ]);
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Token could not be invalidated: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to log out, please try again: ' . $e->getMessage()
            ], 500);
        }
    }
}
