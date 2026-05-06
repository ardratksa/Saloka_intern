<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('is_leader')) {
            $query->where('is_leader', $request->boolean('is_leader'));
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        return response()->json(
            $query->orderBy('name')->get()
                ->map(fn($u) => $this->formatUser($u))
        );
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:100',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|string|min:6',
            'role'      => 'required|in:admin,staff',
            'wa_number' => 'nullable|string|max:20',
            'is_leader' => 'nullable|boolean',
        ]);

        $user = User::create([
            'name'      => $request->name,
            'email'     => $request->email,
            'password'  => bcrypt($request->password),
            'role'      => $request->role,
            'wa_number' => $request->wa_number,
            'is_leader' => $request->boolean('is_leader'),
            'is_active' => true,
        ]);

        return response()->json([
            'message' => 'User berhasil ditambahkan.',
            'user'    => $this->formatUser($user),
        ], 201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name'      => 'sometimes|string|max:100',
            'wa_number' => 'sometimes|nullable|string|max:20',
            'is_leader' => 'sometimes|boolean',
            'is_active' => 'sometimes|boolean',
            'role'      => 'sometimes|in:admin,staff',
        ]);

        $user->update(
            $request->only([
                'name', 'wa_number', 'is_leader', 'is_active', 'role',
            ])
        );

        return response()->json([
            'message' => 'User berhasil diperbarui.',
            'user'    => $this->formatUser($user),
        ]);
    }

    public function updatePhoto(Request $request, User $user)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $path = $request->file('photo')->store('user-photos', 'public');
        $user->update(['photo_path' => $path]);

        return response()->json([
            'message'   => 'Foto berhasil diperbarui.',
            'photo_url' => asset('storage/' . $path),
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
            'is_active' => $user->is_active,
            'wa_number' => $user->wa_number,
            'photo_url' => $user->photo_path
                ? asset('storage/' . $user->photo_path)
                : null,
        ];
    }
}