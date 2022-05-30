<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_faq']['fields']['hofff_contact_profiles'] = [
    'label'            => &$GLOBALS['TL_LANG']['tl_faq']['hofff_contact_profiles'],
    'exclude'          => true,
    'inputType'        => 'picker',
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

$GLOBALS['TL_DCA']['tl_faq']['fields']['hofff_contact_profiles_order'] = ['sql' => 'blob NULL'];
