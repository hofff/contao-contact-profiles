<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Netzmacht\Contao\Toolkit\Dca\DcaManager;

use function is_array;

final class InitializePalettesListener
{
    public function __construct(private DcaManager $dcaManager)
    {
    }

    #[AsCallback('tl_calendar_events', 'config.onload')]
    #[AsCallback('tl_faq', 'config.onload')]
    #[AsCallback('tl_news', 'config.onload')]
    public function initializePalette(DataContainer $dataContainer): void
    {
        $definition = $this->dcaManager->getDefinition($dataContainer->table);

        foreach ($definition->get(['palettes'], []) as $palette => $config) {
            if (is_array($config)) {
                continue;
            }

            PaletteManipulator::create()
                ->addLegend('hofff_contact_profiles_legend', 'title_legend')
                ->addField(
                    'hofff_contact_profiles',
                    'hofff_contact_profiles_legend',
                    PaletteManipulator::POSITION_APPEND,
                )
                ->applyToPalette($palette, $dataContainer->table);
        }
    }
}
