<?php
/**
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */

if (!isset($container)) {
    throw new \Exception('missing container');
}
/** @var \Symfony\Component\DependencyInjection\ContainerInterface $container */

// Set a default unique secret, based on a project-specific entropy value.
if (isset($_ENV['PLATFORM_PROJECT_ENTROPY'])) {
    $container->setParameter('kernel.secret', $_ENV['PLATFORM_PROJECT_ENTROPY']);
}

// Configure host
// si http_host est configuré, alors on ne prend pas le host de platform
// mais celui configuré en dur. cette règle permet de générer des URL en dur
// avec un http_host principal même si plusieurs domaines sont configurés
// pour le projet
if (isset($_ENV['PLATFORM_ROUTES']) && $container->getParameter('http_host') === 'localhost') {
    $routes = json_decode(base64_decode($_ENV['PLATFORM_ROUTES']), true);

    foreach ($routes as $route => $routeInfo) {
        if ($routeInfo['type'] === 'upstream') {
            $host = parse_url($route, PHP_URL_HOST);
            $container->setParameter('http_host', $host);
            break;
        }
    }
}
