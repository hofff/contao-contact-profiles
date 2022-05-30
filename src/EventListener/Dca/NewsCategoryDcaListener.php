<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\CoreBundle\ServiceAnnotation\Hook;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;

final class NewsCategoryDcaListener
{
    private DcaManager $dcaManager;

    private array $bundles;

    public function __construct(DcaManager $dcaManager, array $bundles)
    {
        $this->dcaManager = $dcaManager;
        $this->bundles    = $bundles;
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
}
