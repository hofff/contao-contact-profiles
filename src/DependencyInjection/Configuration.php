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
                ->arrayNode('alias')
                    ->info('Alias settings')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('pattern')
                            ->info('Alias pattern, use fields as placeholders {FIELD}')
                            ->defaultValue('{title}-{firstname}-{lastname}')
                        ->end()
                        ->scalarNode('validChars')
                            ->info('Valid caracters')
                            ->defaultValue('a-z0-9')
                        ->end()
                        ->scalarNode('locale')
                            ->info('The locale used to generate the alias used as fallback')
                            ->defaultValue('en')
                        ->end()
                        ->scalarNode('delimiter')
                            ->info('Delimiter for replaced characters')
                            ->defaultValue('-')
                        ->end()
                    ->end()
            ->end();

        return $builder;
    }
}
