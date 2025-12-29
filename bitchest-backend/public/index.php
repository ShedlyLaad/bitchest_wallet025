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
    echo '<h1>Erreur serveur</h1>';
    echo '<p>Le fichier <code>vendor/autoload.php</code> est introuvable.</p>';
    echo '<p>Solution rapide :</p>';
    echo '<ul>';
    echo '<li>À la racine du projet, exécutez : <code>composer install</code></li>';
    echo '<li>Vérifiez que le répertoire <code>vendor/</code> existe et que PHP peut y accéder (permissions)</li>';
    echo '<li>Si le problème persiste, consultez <code>storage/logs/</code></li>';
    echo '</ul>';
    echo '<p>Commande suggérée :</p>';
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
    echo '<h1>Erreur serveur</h1>';
    echo '<p>Le fichier <code>bootstrap/app.php</code> est introuvable.</p>';
    echo '<p>Vérifiez que vous êtes dans le bon répertoire et que tous les fichiers du framework sont présents.</p>';
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
    echo '<h1>Erreur de résolution de dépendance</h1>';
    echo '<p>Target class <strong>'.$e->getMessage().'</strong> introuvable.</p>';
    echo '<p>Vérifiez vos fichiers de route (routes/web.php, routes/api.php) pour des chaînes incorrectes ou un contrôleur mal référencé.</p>';
    echo '<ul>';
    echo '<li>Si vous utilisez "Controller@method", soit ajoutez le $namespace dans RouteServiceProvider, soit utilisez [Controller::class, \'method\'].</li>';
    echo '<li>Videz le cache : <code>php artisan route:clear && php artisan config:clear</code></li>';
    echo '</ul>';
    exit(1);
} catch (Throwable $e) {
    http_response_code(500);
    echo '<h1>Erreur interne de l\'application</h1>';
    echo '<p>Une exception est survenue lors du démarrage de l\'application.</p>';
    echo '<p>Vérifiez : <code>storage/logs/</code>, les permissions et que l\'environnement (.env) est correctement configuré.</p>';
    // Afficher un message minimal en prod ; pour debug local, activer APP_DEBUG dans .env
    exit(1);
}
