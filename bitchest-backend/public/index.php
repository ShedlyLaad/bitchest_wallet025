<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;
use Illuminate\Contracts\Container\BindingResolutionException;

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
*/

$autoload = __DIR__.'/../vendor/autoload.php';
if (!file_exists($autoload)) {
    http_response_code(500);
    echo '<h1>Server Error</h1>';
    echo '<p>The file <code>vendor/autoload.php</code> was not found.</p>';
    echo '<p>Quick solution:</p>';
    echo '<ul>';
    echo '<li>At the project root, run: <code>composer install</code></li>';
    echo '<li>Verify that the <code>vendor/</code> directory exists and PHP can access it (permissions)</li>';
    echo '<li>If the problem persists, check <code>storage/logs/</code></li>';
    echo '</ul>';
    echo '<p>Suggested command:</p>';
    echo '<pre>cd '.htmlspecialchars(realpath(__DIR__.'/..')).' && composer install</pre>';
    exit(1);
}
require $autoload;

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
*/

$appBootstrap = __DIR__.'/../bootstrap/app.php';
if (!file_exists($appBootstrap)) {
    http_response_code(500);
    echo '<h1>Server Error</h1>';
    echo '<p>The file <code>bootstrap/app.php</code> was not found.</p>';
    echo '<p>Verify that you are in the correct directory and that all framework files are present.</p>';
    exit(1);
}

$app = require_once $appBootstrap;

try {
    $kernel = $app->make(Kernel::class);

    $response = $kernel->handle(
        $request = Request::capture()
    );

    $response->send();

    $kernel->terminate($request, $response);
} catch (BindingResolutionException $e) {
    http_response_code(500);
    echo '<h1>Dependency Resolution Error</h1>';
    echo '<p>Target class <strong>'.$e->getMessage().'</strong> not found.</p>';
    echo '<p>Check your route files (routes/web.php, routes/api.php) for incorrect strings or a misreferenced controller.</p>';
    echo '<ul>';
    echo '<li>If you are using "Controller@method", either add the $namespace in RouteServiceProvider, or use [Controller::class, \'method\'].</li>';
    echo '<li>Clear the cache: <code>php artisan route:clear && php artisan config:clear</code></li>';
    echo '</ul>';
    exit(1);
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Internal Application Error</h1>';
    echo '<p>An exception occurred while starting the application.</p>';
    echo '<p>Check: <code>storage/logs/</code>, permissions, and that the environment (.env) is properly configured.</p>';
    exit(1);
}
