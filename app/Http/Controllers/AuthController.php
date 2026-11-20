<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Services\MaintenanceService;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            $user = Auth::user();
            // Jika mode maintenance aktif dan user bukan admin, keluarkan sesi
            if (MaintenanceService::isActive() && !$user->isAdmin()) {
                Auth::logout();
                request()->session()->invalidate();
                request()->session()->regenerateToken();
            } else {
                return $this->smartRedirect($user);
            }
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $loginField = filter_var($request->login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        $credentials = [
            $loginField => $request->login,
            'password' => $request->password,
            'is_active' => true,
        ];

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Jika mode maintenance sedang aktif dan user bukan admin (misal Owner atau Agen):
            if (MaintenanceService::isActive() && !$user->isAdmin()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'login' => 'Mohon maaf server kami sedang dalam perbaikan. Akses saat ini hanya dibuka untuk Administrator.',
                ])->onlyInput('login');
            }

            $request->session()->regenerate();
            return $this->smartRedirect($user);
        }

        return back()->withErrors([
            'login' => 'Kredensial yang dimasukkan tidak cocok dengan data kami atau akun dinonaktifkan.',
        ])->onlyInput('login');
    }

    /**
     * Demo 1-Click Quick Login untuk kemudahan evaluasi & pengujian
     */
    public function quickLogin(string $role)
    {
        if (MaintenanceService::isActive() && $role !== 'admin') {
            return redirect()->route('login')->with('error', 'Mohon maaf server kami sedang dalam perbaikan. Akses demo agen/owner ditutup sementara.');
        }

        $user = User::where('role', $role)->first();
        if ($user) {
            Auth::login($user);
            request()->session()->regenerate();
            return $this->smartRedirect($user);
        }

        return redirect()->route('login')->with('error', 'Akun demo tidak ditemukan.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar dari sistem SPL.');
    }

    private function smartRedirect(User $user)
    {
        if ($user->isOwner() || $user->isAdmin()) {
            return redirect()->intended(route('owner.dashboard'));
        }

        return redirect()->intended(route('agent.feed'));
    }
}

