<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Hofff\Contao\Consent\Bridge\ConsentToolManager;

use function count;

final class ModuleDcaListener
{
    /** @var ConsentToolManager */
    private $consentToolManager;

    public function __construct(ConsentToolManager $consentToolManager)
    {
        $this->consentToolManager = $consentToolManager;
    }

    public function initializePalettes(): void
    {
        if (count($this->consentToolManager->consentTools()) === 0) {
            return;
        }

        PaletteManipulator::create()
            ->addLegend('hofff_consent_bridge_legend', 'expert_legend')
            ->addField(
                ['hofff_contact_consent_tag_youtube', 'hofff_contact_consent_tag_vimeo'],
                'hofff_consent_bridge_legend',
                PaletteManipulator::POSITION_APPEND
            )
            ->applyToPalette('hofff_contact_profile', 'tl_module')
            ->applyToPalette('hofff_contact_profilecustom', 'tl_module')
            ->applyToPalette('hofff_contact_profilecategories', 'tl_module')
            ->applyToPalette('hofff_contact_profiledynamic', 'tl_module')
            ->applyToPalette('hofff_contact_profile_detail', 'tl_module');
    }
}
