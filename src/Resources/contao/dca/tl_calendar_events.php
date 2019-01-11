<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\CalendarEventsDcaListener;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;

$GLOBALS['TL_DCA']['tl_calendar_events']['config']['onload_callback'][] = [
    CalendarEventsDcaListener::class,
    'initializePalette',
];

$GLOBALS['TL_DCA']['tl_calendar_events']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_calendar_events']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];
