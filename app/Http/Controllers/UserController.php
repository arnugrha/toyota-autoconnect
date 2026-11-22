<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuditTrailService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of internal users and customers.
     */
    public function index(Request $request): View
    {
        $query = User::with('roles')->latest();

        if ($request->filled('q')) {
            $keyword = trim($request->input('q'));
            $query->where(function ($sub) use ($keyword) {
                $sub->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%")
                    ->orWhere('phone', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $query->whereHas('roles', fn ($q) => $q->where('name', $roleName));
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->paginate(15)->withQueryString();
        $roles = Role::orderBy('name')->get();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => [
                'q' => $request->input('q', ''),
                'role' => $request->input('role', ''),
                'status' => $request->input('status', ''),
            ],
        ]);
    }

    /**
     * Show the form for creating a new staff user.
     */
    public function create(): View
    {
        $roles = Role::where('name', '!=', 'Customer')->orderBy('name')->get();

        return view('admin.users.create', [
            'roles' => $roles,
        ]);
    }

    /**
     * Store a newly created staff user in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'password' => ['required', 'string', Password::defaults()],
            'is_active' => ['boolean'],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'email_verified_at' => now(),
        ]);

        $user->assignRole($validated['role']);

        return redirect()->route('admin.users.index')
            ->with('status', "Akun staf {$user->name} berhasil dibuat dengan role {$validated['role']}.");
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', [
            'user' => $user,
            'roles' => $roles,
        ]);
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => ['required', 'string', Rule::exists('roles', 'name')],
            'password' => ['nullable', 'string', Password::defaults(), 'confirmed'],
            'is_active' => ['boolean'],
        ], [
            'password.confirmed' => 'Konfirmasi reset kata sandi tidak cocok.',
        ]);

        // Safety check: Prevent currently authenticated Super Admin from deactivating themselves
        if (Auth::id() === $user->id && ! $request->boolean('is_active')) {
            return back()->withErrors([
                'is_active' => 'Anda tidak dapat menonaktifkan akun Anda sendiri saat sedang masuk.',
            ]);
        }

        // Safety check: Prevent removing Super Admin role from yourself if you are the logged in Super Admin
        if (Auth::id() === $user->id && $user->hasRole('Super Admin') && $validated['role'] !== 'Super Admin') {
            return back()->withErrors([
                'role' => 'Anda tidak dapat menurunkan role akun administrator Anda sendiri.',
            ]);
        }

        $payload = [
            'name' => $validated['name'],
            'phone' => $validated['phone'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ];

        $passwordReset = false;
        if (! empty($validated['password'])) {
            $payload['password'] = Hash::make($validated['password']);
            $passwordReset = true;
        }

        $user->update($payload);

        if ($passwordReset) {
            app(AuditTrailService::class)->logAuth('password_reset_by_admin', $user, true);
        }

        $user->syncRoles([$validated['role']]);

        $message = "Data pengguna {$user->name} berhasil diperbarui.";
        if ($passwordReset) {
            $message .= ' Kata sandi akun juga berhasil direset.';
        }

        return redirect()->route('admin.users.index')
            ->with('status', $message);
    }
}
