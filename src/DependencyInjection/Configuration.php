<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder  = new TreeBuilder('hofff_contao_contact_profiles');
        $rootNode = $builder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('sources')
                    ->info('Dynamic sources of the contact profiles')
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
                            ->info('Valid characters')
                            ->defaultValue('a-z0-9')
                        ->end()
                        ->scalarNode('ignoreChars')
                            ->info('Ignored characters')
                            ->defaultValue('\p{Mn}\p{Lm}')
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
                ->end()
                ->arrayNode('multilingual')
                    ->info('Enable the multilingual feature. It requires that terminal42/dc_multilingual is installed')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enable')
                            ->info('Enable multilingual')
                            ->defaultValue(false)
                        ->end()
                        ->scalarNode('fallback_language')
                            ->info('Define a fallback language if a special one should be used')
                        ->end()
                        ->arrayNode('languages')
                            ->info('Supported languages. If not defined all root page languages are used')
                            ->scalarPrototype()
                            ->end()
                        ->end()
                        ->arrayNode('fields')
                            ->info('List of translated fields of the contact profile table')
                            ->defaultValue(
                                [
                                    'alias',
                                    'salutation',
                                    'title',
                                    'position',
                                    'profession',
                                    'caption',
                                    'websiteTitle',
                                    'teaser',
                                    'description',
                                    'statement',
                                    'jumpTo',
                                    'videos',
                                ]
                            )
                            ->scalarPrototype()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $builder;
    }
}
