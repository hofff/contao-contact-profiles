<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\FAQDcaListener;

$GLOBALS['TL_DCA']['tl_faq']['config']['onload_callback'][] = [
    FAQDcaListener::class,
    'initializePalette',
];

$GLOBALS['TL_DCA']['tl_faq']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_faq']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];
