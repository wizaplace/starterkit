<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace Tests\PHPUnit;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use VCR\VCR;
use WizaplaceFrontBundle\Tests\TestEnv\Service\VcrGuzzleMiddleware;

abstract class VcrWebTestCase extends WebTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $kernel = self::bootKernel();
        $kernel->getContainer()->get('cache.app')->clear();

        $vcr = $kernel->getContainer()->get(VcrGuzzleMiddleware::class)->getVcr();

        $vcr->configure()->setCassettePath(dirname((new \ReflectionClass(static::class))->getFileName()));
        $vcr->configure()->enableLibraryHooks([]);
        $vcr->turnOn();
        $cassette = (new \ReflectionClass($this))->getShortName().DIRECTORY_SEPARATOR.$this->getName().'.yml';
        $vcr->insertCassette($cassette);
    }

    protected function tearDown(): void
    {
        self::$kernel->getContainer()->get(VcrGuzzleMiddleware::class)->getVcr()->turnOff();

        parent::tearDown();
    }
}
