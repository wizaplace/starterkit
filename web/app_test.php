<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
use Tests\VcrHelper;
use VCR\VCR;

// If you don't want to setup permissions the proper way, just uncomment the following PHP line
// read http://symfony.com/doc/current/setup.html#checking-symfony-application-configuration-and-setup
// for more information
//umask(0000);

/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';

Debug::enable();

$kernel = new AppKernel('test', true);
$request = Request::createFromGlobals();

if ($request->headers->has('vcr-k7')) {
    VcrHelper::configureVcr(__DIR__.'/../tests/behat/fixtures/VCR/');

    VCR::turnOn();
    VCR::insertCassette($request->headers->get('vcr-k7'));

    // Clear the local cache
    // @TODO: parametrize, so we can test some cases with cache
    $kernel->boot();
    $kernel->getContainer()->get('cache.app')->clear();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

VCR::turnOff();
