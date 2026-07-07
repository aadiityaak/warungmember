<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Response;

class ProfileController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user()->load('member');

        return inertia('member/profile/Index', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'member_code' => $user->member?->member_code,
                'birth_date' => $user->member?->birth_date?->format('Y-m-d'),
                'total_points' => $user->member?->total_points ?? 0,
                'deposit_balance' => $user->member?->deposit_balance ?? 0,
            ],
        ]);
    }

    public function edit(): Response
    {
        $user = Auth::user()->load('member');

        return inertia('member/profile/Edit', [
            'profile' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar' => $user->avatar,
                'birth_date' => $user->member?->birth_date?->format('Y-m-d'),
            ],
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if ($user->role !== 'member') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|string|min:8',
            'birth_date' => 'nullable|date',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                $oldPath = str_replace('/storage/', '', $user->avatar);
                if (Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
            }
            $path = $request->file('avatar')->store('avatars', 'public');
            $data['avatar'] = Storage::url($path);
        }

        $user->update($data);

        if (! empty($validated['password'])) {
            $user->update(['password' => bcrypt($validated['password'])]);
        }

        if ($user->member && array_key_exists('birth_date', $validated)) {
            $user->member->update(['birth_date' => $validated['birth_date']]);
        }

        return redirect()->route('member.profile')
            ->with('toast', ['type' => 'success', 'message' => 'Profil berhasil diperbarui.']);
    }
}
