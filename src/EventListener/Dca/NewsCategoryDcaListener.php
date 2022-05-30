<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\DataContainer;
use Contao\Input;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;

use function array_pop;
use function array_unshift;
use function implode;

final class NewsCategoryDcaListener
{
    private DcaManager $dcaManager;

    private array $bundles;

    private RepositoryManager $repositoryManager;

    public function __construct(DcaManager $dcaManager, RepositoryManager $repositoryManager, array $bundles)
    {
        $this->dcaManager        = $dcaManager;
        $this->bundles           = $bundles;
        $this->repositoryManager = $repositoryManager;
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
        if ($table !== Profile::getTable() || isset($this->bundles['CodefogNewsCategoriesBundle'])) {
            return;
        }

        $this->dcaManager->getDefinition(
            Profile::getTable())->modify(['fields'],
            function (array $fields): array {
                unset ($fields['news_categories']);

                return $fields;
            }
        );
    }

    /** @Callback(table="tl_news_category", target="list.label.label") */
    public function newsCategoryOptions(array $row, ?string $originalLabel, DataContainer $dataContainer): ?string
    {
        if (Input::get('do') !== 'hofff_contact_profiles') {
            return $originalLabel;
        }

        $repository = $this->repositoryManager->getRepository(NewsCategoryModel::class);
        $categories = [];
        $label      = [];

        $categoryId = $row['id'];

        while ($categoryId > 0) {
            $category = $categories[$categoryId] ?? $repository->find($categoryId);
            if ($category === null) {
                break;
            }

            $categoryId = $category->pid;

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
