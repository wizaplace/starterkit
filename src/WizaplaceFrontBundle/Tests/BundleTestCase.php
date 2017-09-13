<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;
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

    /** @var Client */
    protected $client;

    protected function setUp()
    {
        parent::setUp();
        self::$cassetteName = (new \ReflectionClass(static::class))->getShortName().DIRECTORY_SEPARATOR.$this->getName().'_K7.yml';
        $this->client = static::createClient();
    }

    protected static function createClient(array $options = array(), array $server = array())
    {
        $client = parent::createClient($options, $server);
        self::$kernel->getContainer()->get(VcrGuzzleMiddleware::class)->resetRequestIndex();
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
        $this->client = null;
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

    final protected function clearRenderedData(): void
    {
        self::$kernel->getContainer()->get(TwigEngineLogger::class)->clearRenderedData();
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

    final protected function generateCsrfToken(string $tokenId, Client $client): string
    {
        $csrfToken = $client->getContainer()->get('security.csrf.token_manager')->refreshToken($tokenId)->getValue();

        // Put the token in the client's session
        $client->getRequest()->getSession()->set(SessionTokenStorage::SESSION_NAMESPACE.'/'.$tokenId, $csrfToken);
        $client->getRequest()->getSession()->save();

        return $csrfToken;
    }
}
