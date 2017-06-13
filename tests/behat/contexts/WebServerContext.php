<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\MinkExtension\Context\MinkContext;

class WebServerContext extends MinkContext
{
    /**
     * @BeforeScenario
     */
    public function setWindowSize()
    {
        // To be changed if we want to introduce tests for tablet/mobile devices
        $this->getSession()->getDriver()->maximizeWindow();
    }

    /**
     * @BeforeScenario
     */
    public function setWebServerUrl(BeforeScenarioScope $scope)
    {
        $address = getenv('TEST_WEBSERVER_URL');
        if (!$address) {
            throw new \Exception("Env var TEST_WEBSERVER_URL not set");
        }
        $this->setMinkParameter('base_url', $address);

        $this->getSession('chrome')->setRequestHeader('vcr-k7', "{$scope->getFeature()->getTitle()}/{$scope->getScenario()->getTitle()}.yml");
    }
}
