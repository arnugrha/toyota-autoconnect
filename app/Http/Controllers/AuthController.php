<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Recognized administrative / staff roles for dashboard access.
     */
    public const STAFF_ROLES = [
        'Super Admin',
        'Dealer Manager',
        'Sales Manager',
        'Sales Executive',
        'Inventory Officer',
        'Service Advisor',
        'CRM Officer',
    ];

    /**
     * Show the public customer login form.
     */
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user(), false);
        }

        return view('auth.login', [
            'portalType' => 'customer',
        ]);
    }

    /**
     * Show the staff & admin dashboard login form.
     */
    public function showAdminLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user(), false);
        }

        return view('auth.login', [
            'portalType' => 'admin',
        ]);
    }

    /**
     * Handle staff / dashboard authentication attempt with rate limiting.
     */
    public function adminLogin(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|admin|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk portal petugas. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);

            /** @var User $user */
            $user = Auth::user();

            // Check if user is active
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan oleh administrator dealer. Silakan hubungi manajemen showroom.',
                ]);
            }

            // Check if user has staff / administrative role
            if ($user->hasRole('Customer') && ! $user->hasAnyRole(self::STAFF_ROLES)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withInput($request->only('email'))
                    ->withErrors([
                        'email' => 'Halaman ini khusus untuk login petugas dan administrator dealer. Pelanggan silakan masuk melalui halaman Login Pelanggan.',
                    ]);
            }

            $request->session()->regenerate();

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Kombinasi email dan kata sandi yang Anda masukkan tidak sesuai.',
            ]);
    }

    /**
     * Handle an authentication attempt with rate limiting.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = Str::transliterate(Str::lower($request->input('email')).'|'.$request->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Silakan coba lagi dalam {$seconds} detik.",
            ]);
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            RateLimiter::clear($throttleKey);

            /** @var User $user */
            $user = Auth::user();

            // Check if user is active
            if (! $user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun Anda dinonaktifkan oleh administrator dealer. Silakan hubungi manajemen showroom.',
                ]);
            }

            $request->session()->regenerate();

            return $this->redirectBasedOnRole($user);
        }

        RateLimiter::hit($throttleKey, 60);

        return back()->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Kombinasi email dan kata sandi yang Anda masukkan tidak sesuai.',
            ]);
    }

    /**
     * Show the public customer registration form.
     */
    public function showRegister(): View|RedirectResponse
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user(), false);
        }

        return view('auth.register');
    }

    /**
     * Handle public customer registration.
     */
    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', Password::defaults(), 'confirmed'],
            'terms' => ['accepted'],
        ], [
            'terms.accepted' => 'Anda harus menyetujui syarat & ketentuan layanan dealer.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'email.unique' => 'Alamat email ini sudah terdaftar dalam sistem.',
        ]);

        // Secure customer creation: strictly assign Customer role regardless of input
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'is_active' => true,
        ]);

        $user->assignRole('Customer');

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('customer.dashboard')
            ->with('success', 'Selamat datang di Toyota AutoConnect! Akun pelanggan Anda berhasil dibuat.');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Anda telah berhasil keluar.');
    }

    /**
     * Determine redirect destination based on user role.
     */
    protected function redirectBasedOnRole(User $user, bool $useIntended = true): RedirectResponse
    {
        $destination = $user->hasRole('Customer')
            ? route('customer.dashboard')
            : route('admin.dashboard');

        return $useIntended ? redirect()->intended($destination) : redirect($destination);
    }
}
