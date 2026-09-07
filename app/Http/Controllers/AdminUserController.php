<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:60'],
        ]);

        $users = User::query()
            ->with('roles:id,name,label')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['role'] ?? null, function ($query, string $role) {
                $query->whereHas('roles', fn ($query) => $query->where('name', $role));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString()
            ->through(fn (User $user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'locale' => $user->locale,
                'is_active' => $user->is_active,
                'created_at' => $user->created_at?->toDateString(),
                'roles' => $user->roles->map(fn (Role $role) => [
                    'id' => $role->id,
                    'name' => $role->name,
                    'label' => $role->label,
                ]),
            ]);

        return Inertia::render('Admin/Users', [
            'users' => $users,
            'roles' => Role::query()
                ->orderBy('label')
                ->get(['id', 'name', 'label']),
            'filters' => [
                'search' => $filters['search'] ?? '',
                'role' => $filters['role'] ?? '',
            ],
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'role_ids' => ['required', 'array', 'min:1'],
            'role_ids.*' => ['integer', Rule::exists('roles', 'id')],
            'is_active' => ['required', 'boolean'],
        ]);

        $selectedRoles = Role::query()
            ->whereIn('id', $validated['role_ids'])
            ->get();

        abort_if($selectedRoles->isEmpty(), 422, 'At least one role is required.');

        $removesOwnAdminRole = $request->user()->is($user)
            && $user->hasRole(Role::SUPER_ADMIN)
            && $selectedRoles->where('name', Role::SUPER_ADMIN)->isEmpty();

        abort_if($removesOwnAdminRole, 422, 'You cannot remove your own Super Admin role.');
        abort_if($request->user()->is($user) && $validated['is_active'] === false, 422, 'You cannot deactivate your own account.');

        $user->roles()->sync($selectedRoles->pluck('id'));
        $user->forceFill(['is_active' => $validated['is_active']])->save();

        return back()->with('success', $user->name.' has been updated.');
    }
}
