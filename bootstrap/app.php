<?php

use App\Services\ExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withSingletons([
        \Faker\Generator::class => function () {
            $faker = \Faker\Factory::create();
            $faker->addProvider(new \ToneflixCode\FakerLoremToneflix\FakerLoremToneflixProvider($faker));

            return $faker;
        },
    ])
    ->withBindings(
        [
            \Faker\Generator::class.':en_NG' => \Faker\Generator::class,
            \Faker\Generator::class.':en_GB' => \Faker\Generator::class,
            \Faker\Generator::class.':en_US' => \Faker\Generator::class,
        ]
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->statefulApi();
        $middleware->throttleApi();

        $middleware->validateCsrfTokens(except: [
            'api/*',
            'api.*',
        ]);

        $middleware->preventRequestsDuringMaintenance();

        $middleware->convertEmptyStringsToNull();

        $middleware->trimStrings(
            except: [
                'current_password',
                'password',
                'password_confirmation',
            ]
        );

        $middleware->trustProxies(
            at: null,
            headers: Request::HEADER_X_FORWARDED_FOR |
                Request::HEADER_X_FORWARDED_HOST |
                Request::HEADER_X_FORWARDED_PORT |
                Request::HEADER_X_FORWARDED_PROTO |
                Request::HEADER_X_FORWARDED_AWS_ELB
        );

        $middleware->api(append: [
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ]);

        $middleware->alias([
            'auth' => \Illuminate\Auth\Middleware\Authenticate::class,
            'guest' => \Illuminate\Auth\Middleware\RedirectIfAuthenticated::class,
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        if (config('app.testing', false) === false) {
            $exceptions->render(function (Throwable $e, Request $request) {
                return (new ExceptionHandler())->render($request, $e);
            });
        }
    })->create();
