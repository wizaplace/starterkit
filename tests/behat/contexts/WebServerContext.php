<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Behat\MinkExtension\Context\MinkContext;
use Behat\Symfony2Extension\Context\KernelDictionary;
use Symfony\Bundle\WebServerBundle\WebServer;
use Symfony\Bundle\WebServerBundle\WebServerConfig;

class WebServerContext extends MinkContext
{
    use KernelDictionary;

    /** @var WebServer */
    private static $webServer;

    /** @var string */
    private static $pidFile;

    /**
     * @BeforeScenario
     */
    public function startWebServer(): void
    {
        self::$pidFile = $this->getContainer()->getParameter('kernel.project_dir').'/var/webserver.pid';
        $webRoot = $this->getContainer()->getParameter('kernel.project_dir').'/web';
        $webServerConfig = new WebServerConfig($webRoot, 'test');
        self::$webServer = new WebServer($webServerConfig);
        if (!self::$webServer->isRunning(self::$pidFile)) {
            self::$webServer->start($webServerConfig, self::$pidFile);
        }

        // Wait for the webserver to actually be started.
        $address = false;
        for ($i = 0; $i < 10; $i++) {
            $address = self::$webServer->getAddress(self::$pidFile);
            if ($address !== false) {
                break;
            }
            usleep(100000);
        }
        if ($address === false) {
            throw new \Exception('Webserver took too long to start');
        }

        $address = 'http://'.self::$webServer->getAddress(self::$pidFile);
        $this->setMinkParameter('base_url', $address);
    }

    /**
     * @AfterSuite
     */
    public static function stopWebServer(): void
    {
        if (isset(self::$webServer) && self::$webServer->isRunning(self::$pidFile)) {
            self::$webServer->stop(self::$pidFile);
        }
    }
}
