<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\behat\contexts;

use Behat\Behat\Hook\Scope\AfterScenarioScope;
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
        $this->getSession()->getDriver()->resizeWindow(1366, 768);
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

        $cassette = "{$scope->getFeature()->getTitle()}/{$scope->getScenario()->getTitle()}.yml";

        if (!file_exists(__DIR__.'/../fixtures/VCR/'.$cassette)) {
            $this->getSession('chrome')->setRequestHeader('vcr-new', '1');
        }
        $this->getSession('chrome')->setRequestHeader('vcr-k7', $cassette);
    }

    /**
     * @AfterScenario
     */
    public function recordClues(AfterScenarioScope $scope)
    {
        if (!$scope->getTestResult()->isPassed()) {
            $dirPath = __DIR__."/../../../var/screenshots/{$scope->getFeature()->getTitle()}";
            if (!file_exists($dirPath)) {
                mkdir($dirPath, 0777, true);
            }
            $this->saveScreenshot("{$scope->getScenario()->getTitle()}.png", $dirPath);
        }
    }
}
