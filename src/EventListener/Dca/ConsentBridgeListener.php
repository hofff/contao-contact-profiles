<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Hofff\Contao\Consent\Bridge\ConsentToolManager;

use function count;

final class ConsentBridgeListener
{
    private ConsentToolManager $consentToolManager;

    public function __construct(ConsentToolManager $consentToolManager)
    {
        $this->consentToolManager = $consentToolManager;
    }

    /**
     * @Callback(table="tl_module", target="config.onload")
     * @Callback(table="tl_content", target="config.onload")
     */
    public function initializePalettes(DataContainer $dataContainer): void
    {
        if (count($this->consentToolManager->consentTools()) === 1) {
            return;
        }

        PaletteManipulator::create()
            ->addLegend('hofff_consent_bridge_legend', 'expert_legend')
            ->addField(
                ['hofff_contact_consent_tag_youtube', 'hofff_contact_consent_tag_vimeo'],
                'hofff_consent_bridge_legend',
                PaletteManipulator::POSITION_APPEND
            )
            ->applyToPalette('hofff_contact_profile_list', $dataContainer->table)
            ->applyToPalette('hofff_contact_profile_listcustom', $dataContainer->table)
            ->applyToPalette('hofff_contact_profile_listcategories', $dataContainer->table)
            ->applyToPalette('hofff_contact_profile_listdynamic', $dataContainer->table)
            ->applyToPalette('hofff_contact_profile_detail', $dataContainer->table);
    }
}
