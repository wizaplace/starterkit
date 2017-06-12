<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Behat\MinkExtension\Context\MinkContext;
use Symfony\Bundle\WebServerBundle\WebServer;
use Symfony\Bundle\WebServerBundle\WebServerConfig;

class WebServerContext extends MinkContext
{
    /**
     * @BeforeSuite
     */
    public static function startWebServer(): void
    {
        if (self::getWebServerlUrlFromEnv()) {
            return;
        }
        $webRoot = __DIR__.'/../../../web';
        $webServerConfig = new WebServerConfig($webRoot, 'test');
        $webServer = new WebServer();
        if (!$webServer->isRunning()) {
            $webServer->start($webServerConfig);
        }

        // Wait for the webserver to actually be started.
        $runs = false;
        for ($i = 0; $i < 10; $i++) {
            $runs = $webServer->isRunning();
            if ($runs) {
                break;
            }
            sleep(1);
        }
        if (!$runs) {
            throw new \Exception('Webserver is not running (took too long to start, or already crashed)');
        }
    }

    /**
     * @BeforeStep
     */
    public function setWebServerUrl()
    {
        $address = self::getWebServerlUrlFromEnv();
        if (!$address) {
            $address = 'http://'.(new WebServer())->getAddress();
        }
        $this->setMinkParameter('base_url', $address);
    }

    /**
     * @AfterSuite
     */
    public static function stopWebServer(): void
    {
        $webServer = (new WebServer());
        if ($webServer->isRunning()) {
            $webServer->stop();
        }
    }

    private static function getWebServerlUrlFromEnv()
    {
        return getenv('TEST_WEBSERVER_URL');
    }
}
