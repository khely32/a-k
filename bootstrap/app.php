<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Registered outside the "web" group on purpose. Routes in
            // routes/web.php run through StartSession (SESSION_DRIVER=database),
            // so when the database is down they all return 500 and a probe
            // placed there cannot report why. This one has no session, no CSRF
            // and no database middleware, so it keeps answering.
            Route::get('/healthz', [\App\Http\Controllers\HealthController::class, 'check'])->name('healthz');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'branch.active' => \App\Http\Middleware\CheckBranchActive::class,
        ]);

        // These must run inside the "web" group (after StartSession), not as
        // global middleware. As global middleware they executed before the
        // session was started, so Auth::user() was always null (both checks
        // were dead code) and any code path reaching $request->session()
        // threw "Session store not set on request" -> HTTP 500.
        $middleware->web(append: [
            \App\Http\Middleware\SingleSession::class,
            \App\Http\Middleware\CheckBranchActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
