<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;

final class InitializePalettesListener
{
    /**
     * @Callback(table="tl_calendar_events", target="config.onload")
     * @Callback(table="tl_faq", target="config.onload")
     * @Callback(table="tl_news", target="config.onload")
     */
    public function initializePalette(DataContainer $dataContainer): void
    {
        PaletteManipulator::create()
            ->addLegend('hofff_contact_profiles_legend', 'title_legend')
            ->addField('hofff_contact_profiles', 'hofff_contact_profiles_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', $dataContainer->table);
    }
}