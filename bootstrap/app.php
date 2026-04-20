<?php

use App\Http\Middleware\EnsureIsAdmin;
use App\Http\Middleware\ForceHttpsInProduction;
use App\Http\Middleware\IssueIdToken;
use App\Http\Middleware\RequirePkce;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(ForceHttpsInProduction::class);
        $middleware->append(RequirePkce::class);
        $middleware->append(IssueIdToken::class);

        $middleware->alias([
            'admin' => EnsureIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
