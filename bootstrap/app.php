<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureLecturer;
use App\Http\Middleware\EnsureStudent;
use App\Http\Middleware\HandleInertiaRequests;
use App\Support\SetupDiagnosis;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'lecturer' => EnsureLecturer::class,
            'student' => EnsureStudent::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (Throwable $e, $request) {
            $diag = SetupDiagnosis::diagnose($e);

            if ($diag === null) {
                return null;
            }

            return response()->view('errors.setup', [
                'diag' => $diag,
                'technical' => config('app.debug')
                    ? get_class($e).': '.$e->getMessage()
                    : null,
            ], 503);
        });
    })->create();
