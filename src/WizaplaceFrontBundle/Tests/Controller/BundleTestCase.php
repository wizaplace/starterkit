<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\Controller;

use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use WizaplaceFrontBundle\Tests\TestEnv\Service\TwigEngineLogger;
use WizaplaceFrontBundle\Tests\TestEnv\Service\VcrGuzzleMiddleware;
use WizaplaceFrontBundle\Tests\TestEnv\TestKernel;

/**
 * Test only this bundle, with the kernel {@see \WizaplaceFrontBundle\Tests\TestEnv\TestKernel}
 */
abstract class BundleTestCase extends WebTestCase
{
    /** @var null|string */
    private static $cassetteName;

    protected static $class = TestKernel::class;

    protected function setUp()
    {
        parent::setUp();
        self::$cassetteName = (new \ReflectionClass(static::class))->getShortName().'_K7.yml';
    }

    protected static function createClient(array $options = array(), array $server = array())
    {
        $client = parent::createClient($options, $server);
        $vcr = self::$kernel->getContainer()->get(VcrGuzzleMiddleware::class)->getVcr();

        $vcr->configure()->setCassettePath(dirname((new \ReflectionClass(static::class))->getFileName()));
        $vcr->configure()->enableLibraryHooks([]);

        $vcr->turnOn();
        $vcr->insertCassette(self::$cassetteName);

        return $client;
    }

    protected function tearDown()
    {
        self::$cassetteName = null;
        self::$kernel->getContainer()->get(VcrGuzzleMiddleware::class)->getVcr()->turnOff();
        parent::tearDown();
    }

    /**
     * Given a template name:
     * - if the template was rendered, returns the parameters that were passed to it
     * - if the template was not rendered, fails the test
     */
    final protected function getRenderedData(string $templateName): array
    {
        $fullRenderedData = self::$kernel->getContainer()->get(TwigEngineLogger::class)->getRenderedData();

        $this->assertCount(1, $fullRenderedData[$templateName]);

        return $fullRenderedData[$templateName][0]['parameters'];
    }

    /**
     * Checks the latest response code received by the client, and print debug information in case of failure
     */
    final protected function assertResponseCodeEquals(int $expectedCode, Client $client): void
    {
        $response = $client->getResponse();
        $request = $client->getRequest();

        $message = <<<MSG
"{$request->getMethod()} {$request->getUri()}" resulted in code {$response->getStatusCode()} instead of expected code {$expectedCode}
== Request headers:
{$request->headers}

== Request content:
{$request->getContent()}

== Response headers:
{$response->headers}

== Response content:
{$response->getContent()}
MSG;


        $this->assertSame($expectedCode, $response->getStatusCode(), $message);
    }
}
