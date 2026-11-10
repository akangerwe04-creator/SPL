<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admin memiliki hak akses penuh sama seperti Owner
        $allowedRoles = $roles;
        if (in_array('owner', $roles)) {
            $allowedRoles[] = 'admin';
        }

        if (!in_array($user->role, $allowedRoles)) {
            if ($user->isOwner() || $user->isAdmin()) {
                return redirect()->route('owner.dashboard')->with('error', 'Akses dialihkan ke Dasbor.');
            }
            return redirect()->route('agent.feed')->with('error', 'Anda tidak memiliki hak akses ke halaman tersebut.');
        }

        return $next($request);
    }
}

