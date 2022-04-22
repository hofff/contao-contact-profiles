<?php

declare(strict_types=1);

use Doctrine\DBAL\Platforms\AbstractMySQLPlatform;
use Hofff\Contao\ContactProfiles\EventListener\Dca\CalendarEventsDcaListener;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;

$GLOBALS['TL_DCA']['tl_calendar_events']['config']['onload_callback'][] = [
    CalendarEventsDcaListener::class,
    'initializePalette',
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_calendar_events']['hofff_contact_profiles'],
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

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['hofff_contact_profiles_order'] = [
    'sql' => [
        'type'    => 'blob',
        'length'  => AbstractMySQLPlatform::LENGTH_LIMIT_BLOB,
        'notnull' => false,
    ],
];
