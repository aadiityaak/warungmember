<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Reward;
use Illuminate\Http\Request;
use Inertia\Response;

class RewardController extends Controller
{
    public function index(): Response
    {
        return inertia('admin/rewards/Index', [
            'rewards' => Reward::latest()->paginate(10),
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/rewards/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        Reward::create($validated);

        return redirect()->route('admin.rewards.index')
            ->with('toast', ['type' => 'success', 'message' => 'Reward berhasil ditambahkan.']);
    }

    public function edit(Reward $reward): Response
    {
        return inertia('admin/rewards/Edit', [
            'reward' => $reward,
        ]);
    }

    public function update(Request $request, Reward $reward)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'points_required' => 'required|integer|min:1',
            'stock' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $reward->update($validated);

        return redirect()->route('admin.rewards.index')
            ->with('toast', ['type' => 'success', 'message' => 'Reward berhasil diperbarui.']);
    }

    public function destroy(Reward $reward)
    {
        $reward->delete();

        return redirect()->route('admin.rewards.index')
            ->with('toast', ['type' => 'success', 'message' => 'Reward berhasil dihapus.']);
    }
}
