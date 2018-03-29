<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

if (getenv('PLATFORM_BRANCH') === 'master') {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

Debug::enable();

$kernel = new AppKernel('dev', true);
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
