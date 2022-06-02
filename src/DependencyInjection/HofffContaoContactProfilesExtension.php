<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\DependencyInjection;

use Hofff\Contao\ContactProfiles\EventListener\Dca\NewsCategoryDcaListener;
use Hofff\Contao\ContactProfiles\EventListener\DynamicSource\EventsContactProfilesListener;
use Hofff\Contao\ContactProfiles\EventListener\DynamicSource\FAQContactProfilesListener;
use Hofff\Contao\ContactProfiles\EventListener\DynamicSource\NewsContactProfilesListener;
use Hofff\Contao\ContactProfiles\EventListener\Hook\LanguageRelationsListener;
use Hofff\Contao\ContactProfiles\EventListener\MultilingualListener;
use Hofff\Contao\ContactProfiles\Model\Category\CategoryRepository;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Model\Responsibility\ResponsibilityRepository;
use Hofff\Contao\ContactProfiles\Model\SocialAccount\SocialAccountRepository;
use Netzmacht\Contao\Toolkit\Data\Model\Repository;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * @psalm-type TMultilingaulConfig = array{
 *     enable: bool,
 *     languages?: list<string>|null,
 *     fallbackLanguage: ?string,
 *     fields: list<string>|null,
 * }
 * @SuppressWarnings(PHPMD.LongVariable)
 */
final class HofffContaoContactProfilesExtension extends Extension
{
    /** @var list<class-string<Repository>> */
    private array $multilingualRepositories = [
        CategoryRepository::class,
        ProfileRepository::class,
        ResponsibilityRepository::class,
        SocialAccountRepository::class,
    ];

    /** {@inheritDoc} */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('services.xml');
        $loader->load('repositories.xml');
        $loader->load('listener.xml');

        $config  = $this->processConfiguration(new Configuration(), $configs);
        $sources = $config['sources'];

        $this->configureMultilingual($config['multilingual'], $container, $loader);
        $this->checkCalendarBundle($container, $sources);
        $this->checkFaqBundle($container, $sources);
        $this->checkNewsBundle($container, $sources);
        $this->checkNewsCategoriesBundle($container, $sources);
        $this->checkLanguageRelationsBundle($container, $config['multilingual']['enable']);

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

    /** @param list<string> $sources */
    private function checkNewsCategoriesBundle(ContainerBuilder $container, array &$sources): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if (isset($bundles['CodefogNewsCategoriesBundle'])) {
            $sources[] = 'news_categories';

            return;
        }

        $container->removeDefinition(NewsContactProfilesListener::class);
        $container->removeDefinition(NewsCategoryDcaListener::class);
    }

    /**
     * @param array<string,mixed> $multilingual
     *
     * @psalm-param TMultilingaulConfig $multilingual
     */
    private function configureMultilingual(
        array $multilingual,
        ContainerBuilder $container,
        LoaderInterface $loader
    ): void {
        $container->setParameter('hofff_contao_contact_profiles.multilingual.enable', $multilingual['enable']);
        $container->setParameter('hofff_contao_contact_profiles.multilingual.fields', $multilingual['fields']);
        $container->setParameter(
            'hofff_contao_contact_profiles.multilingual.languages',
            $multilingual['languages'] ?? null
        );
        $container->setParameter(
            'hofff_contao_contact_profiles.multilingual.fallback_language',
            $multilingual['fallback_language'] ?? null
        );

        if (! $multilingual['enable']) {
            $container->removeDefinition(MultilingualListener::class);

            return;
        }

        $bundles = $container->getParameter('kernel.bundles');
        if (! isset($bundles['Terminal42DcMultilingualBundle'])) {
            throw new InvalidConfigurationException(
                'Enable multilingual support of contact profiles requires terminal42/dc_multilingual'
            );
        }

        $loader->load('multilingual.xml');

        $parameters = $container->getParameterBag();
        foreach ($this->multilingualRepositories as $repository) {
            $definition = $container->getDefinition($repository);
            $definition->addTag(
                'netzmacht.contao_toolkit.repository',
                [
                    'model' => $parameters->resolveValue($definition->getArgument(0)),
                ]
            );
        }
    }

    private function checkLanguageRelationsBundle(ContainerBuilder $container, bool $multilingual): void
    {
        $bundles = $container->getParameter('kernel.bundles');
        if ($multilingual && isset($bundles['HofffContaoLanguageRelationsBundle'])) {
            return;
        }

        $container->removeDefinition(LanguageRelationsListener::class);
    }
}
