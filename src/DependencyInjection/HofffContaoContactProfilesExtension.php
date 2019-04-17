<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection;

use Hofff\Contao\ContactProfiles\EventListener\EventsContactProfilesListener;
use Hofff\Contao\ContactProfiles\EventListener\FAQContactProfilesListener;
use Hofff\Contao\ContactProfiles\EventListener\NewsContactProfilesListener;
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

        $this->checkEventsListener($container);
        $this->checkFaqListener($container);
        $this->checkNewsListener($container);
    }

    private function checkEventsListener(ContainerBuilder $container) : void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoCalendarBundle'])) {
            return;
        }

        $container->removeDefinition(EventsContactProfilesListener::class);
    }

    private function checkFaqListener(ContainerBuilder $container) : void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoFaqBundle'])) {
            return;
        }

        $container->removeDefinition(FAQContactProfilesListener::class);
    }

    private function checkNewsListener(ContainerBuilder $container) : void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoNewsBundle'])) {
            return;
        }

        $container->removeDefinition(NewsContactProfilesListener::class);
    }
}
