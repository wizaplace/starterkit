<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\Tests\TestEnv;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;
use WizaplaceFrontBundle\WizaplaceFrontBundle;

/**
 * Kernel meant for testing only.
 * Contains only this bundle and its dependencies
 */
class TestKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = [
            // dependencies
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new \Symfony\Bundle\TwigBundle\TwigBundle(),
            new \Symfony\Bundle\MonologBundle\MonologBundle(),
            new \Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            // test bundles
            new \Symfony\Bundle\DebugBundle\DebugBundle(),
            new \Symfony\Bundle\WebProfilerBundle\WebProfilerBundle(),
            // bundle
            new WizaplaceFrontBundle(),
        ];

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        // We don't need that Environment stuff, just one config
        $loader->load(__DIR__.'/config.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getProjectDir()
    {
        return __DIR__.'/../../../../';
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'var/WizaplaceFrontBundle/cache';
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'var/WizaplaceFrontBundle/logs';
    }
}
