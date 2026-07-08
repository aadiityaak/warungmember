<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

class KasirController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/kasir/Index', [
            'kasirs' => User::where('role', 'kasir')->with('outlet')->latest()->paginate(10, ['id', 'name', 'email', 'created_at']),
        ]);
    }

    public function create(): Response
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        return inertia('admin/kasir/Create');
    }

    public function store(Request $request)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        User::create([
            ...$validated,
            'role' => 'kasir',
            'email_verified_at' => now(),
        ]);

        return redirect()->route('admin.kasir.index')
            ->with('toast', ['type' => 'success', 'message' => 'Kasir berhasil ditambahkan.']);
    }

    public function edit(User $kasir): Response
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        return inertia('admin/kasir/Edit', [
            'kasir' => $kasir->only(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, User $kasir)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$kasir->id,
            'password' => 'nullable|string|min:6',
        ]);

        $data = ['name' => $validated['name'], 'email' => $validated['email']];
        if ($validated['password']) {
            $data['password'] = $validated['password'];
        }

        $kasir->update($data);

        return redirect()->route('admin.kasir.index')
            ->with('toast', ['type' => 'success', 'message' => 'Kasir berhasil diperbarui.']);
    }

    public function destroy(User $kasir)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $kasir->delete();

        return redirect()->route('admin.kasir.index')
            ->with('toast', ['type' => 'success', 'message' => 'Kasir berhasil dihapus.']);
    }
}
