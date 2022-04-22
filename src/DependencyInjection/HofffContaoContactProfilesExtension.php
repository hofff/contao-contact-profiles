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
    /** {@inheritDoc} */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('listener.xml');

        $config  = $this->processConfiguration(new Configuration(), $configs);
        $sources = $config['sources'];

        $this->checkCalendarBundle($container, $sources);
        $this->checkFaqBundle($container, $sources);
        $this->checkNewsBundle($container, $sources);

        $container->setParameter('hofff_contao_contact_profiles.sources', $sources);

        $container->setParameter('hofff_contao_contact_profiles.alias_pattern', $config['alias']['pattern']);
        unset($config['alias']['pattern']);
        $container->setParameter('hofff_contao_contact_profiles.alias_options', $config['alias']);
    }

    /** @param list<string> $sources */
    private function checkCalendarBundle(ContainerBuilder $container, array &$sources): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoCalendarBundle'])) {
            $sources[] = 'event';

            return;
        }

        $container->removeDefinition(EventsContactProfilesListener::class);
    }

    /** @param list<string> $sources */
    private function checkFaqBundle(ContainerBuilder $container, array &$sources): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoFaqBundle'])) {
            $sources[] = 'faq';

            return;
        }

        $container->removeDefinition(FAQContactProfilesListener::class);
    }

    /** @param list<string> $sources */
    private function checkNewsBundle(ContainerBuilder $container, array &$sources): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['ContaoNewsBundle'])) {
            $sources[] = 'news';

            return;
        }

        $container->removeDefinition(NewsContactProfilesListener::class);
    }
}
