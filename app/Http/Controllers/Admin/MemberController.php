<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Response;

class MemberController extends Controller
{
    public function index(Request $request): Response
    {
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        $allowed = ['name', 'email', 'created_at', 'total_points'];
        if (! in_array($sort, $allowed)) {
            $sort = 'created_at';
        }
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        $members = User::where('role', 'member')
            ->when($request->search, fn ($q, $search) => $q->where(fn ($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
            )
            )
            ->with('member')
            ->when($sort === 'total_points', fn ($q) => $q->leftJoin('members', 'users.id', '=', 'members.user_id')
                ->orderBy('members.total_points', $direction)
                ->select('users.*')
            )
            ->when($sort !== 'total_points', fn ($q) => $q->orderBy($sort, $direction))
            ->paginate(20)
            ->withQueryString();

        return inertia('admin/members/Index', [
            'members' => $members,
            'filters' => $request->only('search', 'sort', 'direction'),
        ]);
    }

    public function show(User $member): Response
    {
        if ($member->role !== 'member') {
            abort(404);
        }

        $member->load('member');

        $depositHistory = $member->member?->depositTransactions()
            ->latest('created_at')
            ->paginate(20);

        $pointHistory = $member->member?->pointTransactions()
            ->latest('created_at')
            ->paginate(20);

        return inertia('admin/members/Show', [
            'member' => $member,
            'depositHistory' => $depositHistory,
            'pointHistory' => $pointHistory,
        ]);
    }

    public function create(): Response
    {
        return inertia('admin/members/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $validated['role'] = 'member';
        $validated['password'] = bcrypt($validated['password']);

        DB::transaction(function () use ($validated) {
            $user = User::create($validated);
            Member::create(['user_id' => $user->id]);
        });

        return redirect()->route('admin.members.index')
            ->with('toast', ['type' => 'success', 'message' => 'Member berhasil ditambahkan.']);
    }

    public function edit(User $member): Response
    {
        if ($member->role !== 'member') {
            abort(404);
        }

        return inertia('admin/members/Edit', [
            'member' => $member,
        ]);
    }

    public function update(Request $request, User $member)
    {
        if ($member->role !== 'member') {
            abort(404);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$member->id,
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = bcrypt($validated['password']);
        }

        $member->update($data);

        return redirect()->route('admin.members.index')
            ->with('toast', ['type' => 'success', 'message' => 'Member berhasil diperbarui.']);
    }

    public function destroy(User $member)
    {
        if ($member->role !== 'member') {
            abort(404);
        }

        if (auth()->user()?->role !== 'admin') {
            abort(403);
        }

        $member->delete(); // soft delete

        return redirect()->route('admin.members.index')
            ->with('toast', ['type' => 'success', 'message' => 'Member berhasil dinonaktifkan.']);
    }
}
