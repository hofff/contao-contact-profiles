<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\NewsDcaListener;

$GLOBALS['TL_DCA']['tl_news']['config']['onload_callback'][] = [
    NewsDcaListener::class,
    'initializePalette',
];

$GLOBALS['TL_DCA']['tl_news']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_news']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'picker',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => [
        'orderField' => 'hofff_contact_profiles_order',
        'tl_class'   => 'clr long',
        'multiple'   => true,
        'chosen'     => true,
    ],
    'relation'         => [
        'type'  => 'hasMany',
        'table' => 'tl_contact_profile',
    ],
    'sql'              => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_news']['fields']['hofff_contact_profiles_order'] = ['sql' => 'blob NULL'];
