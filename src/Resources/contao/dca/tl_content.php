<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactFieldsOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactTemplateOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\SourcesOptions;

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][]               = 'hofff_contact_source';
$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile']        = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_fields'
    . ';{redirect_legend:hide},hofff_contact_jump_to'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';
$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_detail'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['metasubselectpalettes']['hofff_contact_source']['custom']     = [
    'hofff_contact_profiles',
    'perPage',
    'numberOfItems',
];
$GLOBALS['TL_DCA']['tl_content']['metasubselectpalettes']['hofff_contact_source']['categories'] = [
    'hofff_contact_categories',
    'perPage',
    'numberOfItems',
];
$GLOBALS['TL_DCA']['tl_content']['metasubselectpalettes']['hofff_contact_source']['dynamic']    = [
    'hofff_contact_sources',
];

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_source'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_source'],
    'exclude'   => true,
    'inputType' => 'select',
    'default'   => 'custom',
    'options'   => ['custom', 'categories', 'dynamic'],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_source_options'],
    'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true, 'helpwizard' => true],
    'sql'       => 'char(16) NOT NULL default \'custom\'',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_sources'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_sources'],
    'exclude'          => true,
    'inputType'        => 'checkbox',
    'options_callback' => [SourcesOptions::class, '__invoke'],
    'reference'        => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_sources_options'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'tinyblob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_categories'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_categories'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'eval'       => ['tl_class' => 'clr', 'multiple' => true, 'mandatory' => true],
    'foreignKey' => 'tl_contact_category.title',
    'sql'        => 'tinyblob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_fields'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_fields'],
    'exclude'          => true,
    'inputType'        => 'checkboxWizard',
    'options_callback' => [ContactFieldsOptions::class, '__invoke'],
    'eval'             => ['tl_class' => 'clr', 'multiple' => true],
    'sql'              => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_template'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_template'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [ContactTemplateOptions::class, '__invoke'],
    'eval'             => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'              => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_more'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_more'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_jump_to'] = [
    'exclude'    => true,
    'inputType'  => 'pageTree',
    'foreignKey' => 'tl_page.title',
    'eval'       => ['fieldType' => 'radio'],
    'sql'        => 'int(10) unsigned NOT NULL default 0',
    'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
];
