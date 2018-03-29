<?php

use Symfony\Component\HttpFoundation\Request;

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

$kernel = new AppKernel('prod', false);
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();

// Uniquement quand on est dans le cluster k8s
if (false !== getenv('KUBERNETES_SERVICE_HOST')) {
    // On fait confiance à toutes les requêtes qui arrivent,  dans le cas ou on est
    // en direct, REMOTE_ADDR correspond à l'adresse réelle du client, dans le cas
    // de k8s, elle correspond à l'adresse de l'ingress
    Request::setTrustedProxies(['127.0.0.1', $request->server->get('REMOTE_ADDR')], Request::HEADER_X_FORWARDED_ALL);
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
