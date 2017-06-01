<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use VCR\VCR;

abstract class VcrWebTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        VCR::turnOn();
        VCR::insertCassette((new \ReflectionClass($this))->getShortName().DIRECTORY_SEPARATOR.$this->getName().'.yml');
    }

    protected function tearDown(): void
    {
        VCR::turnOff();

        parent::tearDown();
    }
}
