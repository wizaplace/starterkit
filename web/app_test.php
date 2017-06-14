<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;
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
    ini_set('opcache.enable', '0');
    VCR::configure()->setCassettePath(__DIR__.'/../tests/behat/fixtures/VCR/');
    VCR::configure()->setMode(VCR::MODE_ONCE);
    VCR::configure()->enableLibraryHooks(['stream_wrapper', 'curl'])
        ->addRequestMatcher('headers_custom_matcher', function (\VCR\Request $first, \VCR\Request $second) {
            $headersBags = [$first->getHeaders(), $second->getHeaders()];

            foreach ($headersBags as &$headers) {
                // Remove flaky headers that we don't care about
                unset($headers['User-Agent']);
            }

            return $headersBags[0] == $headersBags[1];
        })
        ->enableRequestMatchers(array('method', 'url', 'query_string', 'body', 'post_fields', 'headers_custom_matcher'));

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
