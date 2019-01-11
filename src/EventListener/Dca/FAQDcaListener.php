<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;

final class FAQDcaListener
{
    public function initializePalette() : void
    {
        PaletteManipulator::create()
            ->addField('hofff_contact_profiles', 'title_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('default', 'tl_faq');
    }
}
