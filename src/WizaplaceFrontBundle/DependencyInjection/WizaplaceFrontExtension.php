<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use WizaplaceFrontBundle\Controller\HomeController;

class WizaplaceFrontExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

        $loader->load("config_{$container->getParameter('kernel.environment')}.yml");

        $config = $this->processConfiguration(new Configuration(), $configs);
        $container->getDefinition(HomeController::class)
            ->setArgument('$latestProductsMaxCount', $config['home']['latest_products_max_count']);
    }
}
