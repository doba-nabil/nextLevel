<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: [__DIR__.'/../routes/dashboard.php', __DIR__.'/../routes/web.php'],
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        api: [__DIR__.'/../routes/device.php', __DIR__.'/../routes/api.php'],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('localize', [
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRoutes::class,
        ]);
        $middleware->appendToGroup('localizationRedirect', [
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationRedirectFilter::class,
        ]);
        $middleware->appendToGroup('localeSessionRedirect', [
            \Mcamara\LaravelLocalization\Middleware\LocaleSessionRedirect::class,
        ]);
        $middleware->appendToGroup('localeCookieRedirect', [
            \Mcamara\LaravelLocalization\Middleware\LocaleCookieRedirect::class,
        ]);
        $middleware->appendToGroup('localeViewPath', [
            \Mcamara\LaravelLocalization\Middleware\LaravelLocalizationViewPath::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
