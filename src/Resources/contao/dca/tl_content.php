<?php

declare(strict_types=1);

use Hofff\Contao\Consent\Bridge\EventListener\Dca\ConsentIdOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactFieldsOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactTemplateOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContentDcaListener;
use Hofff\Contao\ContactProfiles\EventListener\Dca\SourcesOptions;

/*
 * Config
 */
$GLOBALS['TL_DCA']['tl_content']['config']['onload_callback'][] = [
    ContentDcaListener::class,
    'initializePalettes',
];

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_content']['palettes']['__selector__'][] = 'hofff_contact_source';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_list'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_listcustom'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_profiles,perPage,numberOfItems,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_listcategories'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_categories,perPage,numberOfItems'
    . ',hofff_contact_profiles_order_sql,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_listdynamic'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_sources,hofff_contact_fields'
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

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_initials_filter'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_initials_filtercustom'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_profiles'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_initials_filtercategories'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_categories'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_content']['palettes']['hofff_contact_profile_initials_filterdynamic'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_sources'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

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

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_profiles_order'] = ['sql' => 'blob NULL'];

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

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_profiles_order_sql'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50', 'maxlength' => 128, 'decodeEntities' => true],
    'sql'       => "varchar(128) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_consent_tag_youtube'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_consent_tag_youtube'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [ConsentIdOptions::class, '__invoke'],
    'eval'             => [
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
        'multiple'           => false,
    ],
    'sql'              => ['type' => 'string', 'default' => null, 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['hofff_contact_consent_tag_vimeo'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_content']['hofff_contact_consent_tag_vimeo'],
    'exclude'          => true,
    'inputType'        => 'select',
    'options_callback' => [ConsentIdOptions::class, '__invoke'],
    'eval'             => [
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
        'multiple'           => false,
    ],
    'sql'              => ['type' => 'string', 'default' => null, 'notnull' => false],
];
