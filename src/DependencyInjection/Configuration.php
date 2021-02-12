<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('hofff_contao_contact_profiles');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('sources')
                    ->info('Sources of the contact profiles')
                    ->scalarPrototype()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
