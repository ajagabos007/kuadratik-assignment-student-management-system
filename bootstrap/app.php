<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->name('api.')
                ->group( __DIR__.'/../routes/api.php');
            },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            
            'filter.merge.user_id' => \App\Http\Middleware\MergeUserIdFilter::class,
            'input.merge.user_id' => \App\Http\Middleware\MergeUserIdInput::class,
            'filter.merge.attendance.student' => \App\Http\Middleware\MergeAttendanceStudentFilter::class,
            'teacher' => \App\Http\Middleware\EnsureUserIsTeacher::class,
            
            /**
             * Spatie Permission middleware
             * 
             * @see https://spatie.be/docs/laravel-permission/v6/basic-usage/middleware
             */
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
