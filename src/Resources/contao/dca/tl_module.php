<?php

declare(strict_types=1);

/*
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['__selector__'][] = 'hofff_contact_source';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_list'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_filters,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_listcustom'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_profiles,perPage,numberOfItems,hofff_contact_filters'
    . ',hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_listcategories'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_categories,perPage,numberOfItems'
    . ',hofff_contact_profiles_order_sql,hofff_contact_filters,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_listdynamic'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_sources,hofff_contact_filters,hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_detail'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_fields'
    . ';{template_legend:hide},customTpl,hofff_contact_template,hofff_contact_more,size'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_initials_filter'] = '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_initials_filtercustom'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_profiles'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_initials_filtercategories'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_categories'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_initials_filterdynamic'] =
    '{type_legend},type,headline'
    . ';{profile_legend},hofff_contact_source,hofff_contact_sources'
    . ';{template_legend:hide},customTpl'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID'
    . ';{invisible_legend:hide},invisible,start,stop';


$GLOBALS['TL_DCA']['tl_module']['palettes']['hofff_contact_profile_related_categories'] =
    '{title_legend},name,headline,type'
    . ';{config_legend},news_showEmptyCategories,news_enableCanonicalUrls,showLevel'
    . ';{reference_legend:hide},news_categoriesRoot'
    . ';{redirect_legend:hide},news_forceCategoryUrl,jumpTo'
    . ';{template_legend:hide},navigationTpl,customTpl'
    . ';{image_legend:hide},news_categoryImgSize'
    . ';{protected_legend:hide},protected'
    . ';{expert_legend:hide},guests,cssID,space';

/*
 * Fields
 */
$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_source'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_source'],
    'exclude'   => true,
    'inputType' => 'select',
    'default'   => 'custom',
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_source_options'],
    'eval'      => ['tl_class' => 'clr w50', 'submitOnChange' => true, 'helpwizard' => true],
    'sql'       => 'char(16) NOT NULL default \'custom\'',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_sources'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_sources'],
    'exclude'   => true,
    'inputType' => 'checkbox',
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_sources_options'],
    'eval'      => ['tl_class' => 'clr', 'multiple' => true],
    'sql'       => 'tinyblob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_categories'] = [
    'label'      => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_categories'],
    'exclude'    => true,
    'inputType'  => 'checkbox',
    'eval'       => ['tl_class' => 'clr', 'multiple' => true, 'mandatory' => true],
    'foreignKey' => 'tl_contact_category.title',
    'sql'        => 'tinyblob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_profiles'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_profiles'],
    'inputType' => 'picker',
    'eval'      => [
        'orderField' => 'hofff_contact_profiles_order',
        'tl_class'   => 'clr long',
        'multiple'   => true,
        'chosen'     => true,
    ],
    'relation'  => [
        'type'  => 'hasMany',
        'table' => 'tl_contact_profile',
    ],
    'sql'       => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_profiles_order'] = ['sql' => 'blob NULL'];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_filters'] = [
    'exclude'   => true,
    'inputType' => 'checkbox',
    'options'   => ['initials'],
    'reference' => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_filters_options'],
    'eval'      => ['tl_class' => 'clr', 'multiple' => true, 'helpwizard' => true],
    'sql'       => 'tinyblob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_fields'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_fields'],
    'exclude'   => true,
    'inputType' => 'checkboxWizard',
    'eval'      => ['tl_class' => 'clr', 'multiple' => true],
    'sql'       => 'blob NULL',
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_template'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_template'],
    'exclude'   => true,
    'inputType' => 'select',
    'eval'      => ['includeBlankOption' => true, 'chosen' => true, 'tl_class' => 'w50'],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_more'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_more'],
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50'],
    'sql'       => "varchar(64) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_profiles_order_sql'] = [
    'exclude'   => true,
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50', 'maxlength' => 128, 'decodeEntities' => true],
    'sql'       => "varchar(128) NOT NULL default ''",
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_consent_tag_youtube'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_consent_tag_youtube'],
    'exclude'   => true,
    'inputType' => 'select',
    'eval'      => [
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
        'multiple'           => false,
    ],
    'sql'       => ['type' => 'string', 'default' => null, 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_module']['fields']['hofff_contact_consent_tag_vimeo'] = [
    'label'     => &$GLOBALS['TL_LANG']['tl_module']['hofff_contact_consent_tag_vimeo'],
    'exclude'   => true,
    'inputType' => 'select',
    'eval'      => [
        'tl_class'           => 'w50',
        'includeBlankOption' => true,
        'chosen'             => true,
        'multiple'           => false,
    ],
    'sql'       => ['type' => 'string', 'default' => null, 'notnull' => false],
];
