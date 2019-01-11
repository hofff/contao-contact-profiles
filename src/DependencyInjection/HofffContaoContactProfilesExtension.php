<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection;

use Hofff\Contao\ContactProfiles\EventListener\News\NewsContactProfilesListener;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class HofffContaoContactProfilesExtension extends Extension
{
    /** @param mixed[][] $configs */
    public function load(array $configs, ContainerBuilder $container) : void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('listener.xml');

        $bundles = $container->getParameter('kernel.bundles');
        if (! isset($bundles['ContaoNewsBundle'])) {
            $container->removeDefinition(NewsContactProfilesListener::class);
        }
    }
}
