<?php

declare(strict_types=1);

namespace Isotope\CoreBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('isotope');
        $rootNode = $treeBuilder->getRootNode();
        $rootNode
            ->children()
                ->scalarNode('cart_timeout')
                    ->info('Defines how long guest carts are stored in the database before being purged.')
                    ->cannotBeEmpty()
                    ->defaultValue('30 days')
                    ->validate()
                        ->ifTrue(static fn (string $value) => false === strtotime($value))
                        ->thenInvalid('Enter a valid format that can be parsed by PHP strtotime().')
                    ->end()
                ->end()
                ->scalarNode('order_timeout')
                    ->info('Defines how long an order will stay in the database if the checkout was not successful.')
                    ->cannotBeEmpty()
                    ->defaultValue('7 days')
                    ->validate()
                        ->ifTrue(static fn (string $value) => false === strtotime($value))
                        ->thenInvalid('Enter a valid format that can be parsed by PHP strtotime().')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
