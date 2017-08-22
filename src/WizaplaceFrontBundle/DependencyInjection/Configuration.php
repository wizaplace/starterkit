<?php
/**
 * @author      Wizacha DevTeam <dev@wizacha.com>
 * @copyright   Copyright (c) Wizacha
 * @license     Proprietary
 */
declare(strict_types = 1);

namespace WizaplaceFrontBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('wizaplace');

        $rootNode
            ->children()
                ->arrayNode('home')->addDefaultsIfNotSet()->info('Home page configuration')
                    ->children()
                        ->integerNode('latest_products_max_count')
                            ->treatNullLike(0)
                            ->info('Max number of latest products to be fetched.')
                            ->defaultValue(6)
                            ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
