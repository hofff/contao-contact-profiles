<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactFieldsOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactTemplateOptions;

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile'] = '{type_legend},name,headline,type'
    . ';{profile_legend},hofff_contact_dynamic,hofff_contact_profiles,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_dynamic'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_dynamic'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'eval'             => ['tl_class' => 'clr', 'multiple' => false],
    'sql'              => 'char(1) NOT NULL default \'\'',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_fields'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_fields'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactFieldsOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_template'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_template'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [ContactTemplateOptions::class, '__invoke'],
    'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'              => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_more'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_more'],
    'exclude'          => true,
    'inputType'        => 'text',
    'eval'             => ['tl_class' => 'w50'],
    'sql'              => "varchar(64) NOT NULL default ''",
];
