<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;

class UserManagementController extends Controller
{
    /**
     * Tampilkan seluruh akun dalam sistem (Khusus Akses Admin Tersembunyi)
     */
    public function index(Request $request)
    {
        $query = User::query();

        // 1. Filter Role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        // 3. Pencarian Teks
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15)->withQueryString();

        $stats = [
            'total' => User::count(),
            'owner' => User::where('role', 'owner')->count(),
            'admin' => User::where('role', 'admin')->count(),
            'agent' => User::where('role', 'agent')->count(),
        ];

        return view('admin.users.index', compact('users', 'stats'));
    }

    /**
     * Tambah Akun Baru (Owner, Admin, atau Agen)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:owner,admin,agent',
            'password' => 'required|string|min:4',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['plain_password'] = $validated['password'];
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$validated['name']} (Role: " . strtoupper($validated['role']) . ") berhasil dibuat.");
    }

    /**
     * Perbarui Data Akun (Termasuk Akun Owner dapat diedit oleh Admin)
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|alpha_dash|unique:users,username,' . $user->id,
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone' => 'nullable|string|max:25',
            'address' => 'nullable|string|max:500',
            'role' => 'required|in:owner,admin,agent',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        // Pastikan username dan hak akses Owner utama tidak dapat diubah
        if ($user->username === 'owner') {
            $validated['username'] = 'owner';
            $validated['role'] = 'owner';
            $validated['is_active'] = true;
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', "Data akun {$user->username} berhasil diperbarui.");
    }

    /**
     * Reset Kata Sandi Akun (Termasuk Akun Owner dapat direset oleh Admin)
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'new_password' => 'required|string|min:4',
        ]);

        $user->update([
            'password' => Hash::make($request->new_password),
            'plain_password' => $request->new_password,
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', "Kata sandi untuk akun {$user->username} ({$user->name}) berhasil direset.");
    }

    /**
     * Hapus Akun (Termasuk Akun Owner dapat dihapus oleh Admin)
     */
    public function destroy(User $user)
    {
        // Admin tidak dapat menghapus akunnya sendiri yang sedang aktif
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif login.');
        }

        // Cegah penghapusan akun owner utama permanen
        if ($user->username === 'owner') {
            return back()->with('error', 'Akun Owner utama (owner) dilindungi secara permanen dan tidak dapat dihapus.');
        }

        $userName = $user->name;
        $userRole = strtoupper($user->role);

        DB::transaction(function () use ($user) {
            // Putuskan atau bersihkan dependensi relasi agar tidak melanggar foreign keys
            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = OFF;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            }

            // Hapus data postingan/komentar/like sosial user jika ada
            $user->postLikes()->delete();
            $user->comments()->delete();
            $user->posts()->delete();

            // Jika agen memiliki data konsumen/setoran, bersihkan paket dan konsumen terkait
            foreach ($user->customers as $customer) {
                foreach ($customer->customerPackages as $cp) {
                    $cp->payments()->delete();
                    $cp->customItems()->delete();
                    $cp->partialFulfillment?->items()->delete();
                    $cp->partialFulfillment?->delete();
                    $cp->delete();
                }
                $customer->delete();
            }
            $user->deposits()->delete();

            $user->delete();

            if (DB::getDriverName() === 'sqlite') {
                DB::statement('PRAGMA foreign_keys = ON;');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }
        });

        return redirect()->route('admin.users.index')
            ->with('success', "Akun {$userName} ({$userRole}) berhasil dihapus dari sistem.");
    }
}

