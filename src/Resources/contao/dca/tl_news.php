<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;

PaletteManipulator::create()
    ->addField('hofff_contact_profiles', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('default', 'tl_news');

$GLOBALS['TL_DCA']['tl_news']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_news']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];
