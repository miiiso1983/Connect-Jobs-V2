<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withEvents() // register core event provider & discovery so Registered listener runs
    ->withRouting(

        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => App\Http\Middleware\RoleMiddleware::class,
            'company.approved' => App\Http\Middleware\EnsureCompanyIsApproved::class,
            'setlocale' => App\Http\Middleware\SetLocale::class, // locale from session
            'jwt' => App\Http\Middleware\JwtAuth::class,
            'job.owner' => App\Http\Middleware\EnsureJobBelongsToCompany::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
