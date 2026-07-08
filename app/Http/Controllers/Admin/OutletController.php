<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Response;

class OutletController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/outlets/Index', [
            'outlets' => Outlet::with('kasir')->latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/outlets/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        Outlet::create($validated);

        return redirect()->route('admin.outlets.index')
            ->with('toast', ['type' => 'success', 'message' => 'Outlet berhasil ditambahkan.']);
    }

    public function edit(Outlet $outlet): Response
    {
        return inertia('admin/outlets/Edit', [
            'outlet' => $outlet->load('kasir'),
            'kasirs' => User::where('role', 'kasir')->get(['id', 'name', 'email']),
        ]);
    }

    public function update(Request $request, Outlet $outlet)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $outlet->update($validated);

        return redirect()->route('admin.outlets.index')
            ->with('toast', ['type' => 'success', 'message' => 'Outlet berhasil diperbarui.']);
    }

    public function destroy(Outlet $outlet)
    {
        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $outlet->delete();

        return redirect()->route('admin.outlets.index')
            ->with('toast', ['type' => 'success', 'message' => 'Outlet berhasil dihapus.']);
    }
}
