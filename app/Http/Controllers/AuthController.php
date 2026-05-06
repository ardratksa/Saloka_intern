<?php

namespace App\Http\Controllers;

use App\Models\LocationName;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)
                    ->where('is_active', true)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil.',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_code' => 'required|string',
        ]);

        $location = LocationName::with('type')
            ->where('qr_code', $request->qr_code)
            ->where('is_active', true)
            ->first();

        if (!$location) {
            return response()->json([
                'message' => 'QR tidak valid atau lokasi tidak aktif.',
            ], 404);
        }

        return response()->json([
            'message'  => 'Lokasi ditemukan.',
            'location' => [
                'id'        => $location->id,
                'name'      => $location->name,
                'type_id'   => $location->location_type_id,
                'type_name' => $location->type->name,
            ],
        ]);
    }

    public function me(Request $request)
    {
        return response()->json(
            $this->formatUser($request->user())
        );
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil.',
        ]);
    }

    private function formatUser(User $user): array
    {
        return [
            'id'        => $user->id,
            'name'      => $user->name,
            'email'     => $user->email,
            'role'      => $user->role,
            'is_leader' => $user->is_leader,
            'wa_number' => $user->wa_number,
            'photo_url' => $user->photo_path
                ? asset('storage/' . $user->photo_path)
                : null,
        ];
    }
}