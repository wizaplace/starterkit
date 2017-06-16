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
use Tests\VcrHelper;
use VCR\VCR;

class SymfonyContext extends MinkContext
{
    /**
     * @BeforeSuite
     */
    public static function configureVCR(): void
    {
        VcrHelper::configureVcr(__DIR__.'/../fixtures/VCR/');
    }

    /**
     * @BeforeScenario
     */
    public function startVCR(BeforeScenarioScope $scope)
    {
        VCR::turnOn();
        $cassette = ("{$scope->getFeature()->getTitle()}/{$scope->getScenario()->getTitle()}.yml");
        VCR::insertCassette($cassette);
    }

    /**
     * @AfterScenario
     */
    public function stopVCR()
    {
        VCR::turnOff();
    }
}
