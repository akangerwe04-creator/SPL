<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');

        $middleware->web(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnected::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureDatabaseConnected::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureRole::class,
            'db.connected' => \App\Http\Middleware\EnsureDatabaseConnected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, \Illuminate\Http\Request $request) {
            if ($request->is('login') || $request->is('*/login') || $request->routeIs('login')) {
                return redirect()->route('login')
                    ->withInput($request->except('password', '_token'))
                    ->with('error', 'Sesi login Anda telah disegarkan demi keamanan. Silakan masukkan kata sandi dan klik Masuk kembali.');
            }
            return redirect()->back()
                ->withInput($request->except('password', '_token'))
                ->with('error', 'Sesi halaman telah diperbarui demi keamanan. Silakan klik simpan kembali.');
        });
    })->create();
