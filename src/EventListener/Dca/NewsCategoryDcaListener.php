<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Input;
use Hofff\Contao\ContactProfiles\Frontend\NewsCategories\RelatedNewsCategoriesModule;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

use function array_pop;
use function array_unshift;
use function implode;
use function is_a;
use function sprintf;

final class NewsCategoryDcaListener
{
    private DcaManager $dcaManager;

    private RepositoryManager $repositoryManager;

    public function __construct(DcaManager $dcaManager, RepositoryManager $repositoryManager)
    {
        $this->dcaManager        = $dcaManager;
        $this->repositoryManager = $repositoryManager;
    }

    /**
     * @Hook("initializeSystem")
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function onInitializeSystem(): void
    {
        // Frontend modules
        $GLOBALS['FE_MOD']['hofff_contact_profiles']['hofff_contact_profile_related_categories']
            = RelatedNewsCategoriesModule::class;
    }

    /** @Callback(table="tl_news_category", target="config.onload") */
    public function initializeNewsCategoryPalette(): void
    {
        PaletteManipulator::create()
            ->addLegend('hofff_contact_profile_legend', 'details_legend')
            ->addField('hofff_contact_profiles', 'hofff_contact_profile_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_news_category');
    }

    /** @Hook("loadDataContainer") */
    public function initializeContactProfileFields(string $table): void
    {
        if ($table !== Profile::getTable()) {
            return;
        }

        $this->dcaManager
            ->getDefinition(Profile::getTable())
            ->set(
                ['fields', 'news_categories'],
                [
                    'exclude'   => true,
                    'inputType' => 'picker',
                    'eval'      => [
                        'tl_class'     => 'clr long',
                        'multiple'     => true,
                        'chosen'       => true,
                    ],
                    'relation'  => [
                        'type'          => 'haste-ManyToMany',
                        'table'         => 'tl_news_category',
                        'relationTable' => 'tl_contact_profile_news_category',
                    ],
                ]
            );
    }

    /**
     * @param array<string,mixed> $row
     *
     * @Callback(table="tl_news_category", target="list.label.label")
     */
    public function newsCategoryOptions(array $row, ?string $originalLabel): ?string
    {
        if (Input::get('do') !== 'hofff_contact_profiles') {
            return $originalLabel;
        }

        $repository = $this->repositoryManager->getRepository(NewsCategoryModel::class);
        $categories = [];
        $label      = [];

        if (is_a(NewsCategoryModel::class, Multilingual::class, true)) {
            $langPid    = $this->dcaManager->getDefinition(NewsCategoryModel::getTable())->get(['config', 'langPid']);
            $categoryId = (int) ($row[$langPid] ?: $row['id']);
        } else {
            $categoryId = (int) $row['id'];
        }

        while ($categoryId > 0) {
            /** @psalm-suppress RedundantCondition */
            $category = $categories[$categoryId] ?? $repository->find($categoryId);
            if ($category === null) {
                break;
            }

            $categories[$categoryId] = $category;

            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $categoryId = (int) $category->pid;

            array_unshift($label, $category->getTitle());
        }

        $last = array_pop($label);
        if ($label === []) {
            return $last;
        }

        return sprintf(
            '<span class="tl_gray">%s / </span> %s',
            implode(' / ', $label),
            $last
        );
    }
}
