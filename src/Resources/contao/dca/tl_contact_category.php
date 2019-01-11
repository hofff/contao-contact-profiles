<?php

declare(strict_types=1);

/**
 * Table tl_contact_category
 */
$GLOBALS['TL_DCA']['tl_contact_category'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'ctable'           => ['tl_contact_profile'],
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'doNotCopyRecords' => true,
        'sql'              => [
            'keys' => ['id' => 'primary'],
        ],
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search',
        ],
        'label'             => [
            'fields' => ['title'],
            'format' => '%s',
        ],
        'global_operations' => [
            'responsibilities' => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_category']['responsibilities'],
                'href'  => 'table=tl_contact_responsibility',
                'class' => 'header_hofff_contact_responsibility',
            ],
            'social_accounts' => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_category']['social_accounts'],
                'href'  => 'table=tl_contact_social_account',
                'class' => 'header_hofff_contact_social_accounts',
            ],
        ],
        'operations'        => [
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_category']['edit'],
                'href'  => 'table=tl_contact_profile',
                'icon'  => 'edit.svg',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_category']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.svg',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contact_category']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_category']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => ['default' => '{title_legend},title'],

    // Fields
    'fields'   => [
        'id'         => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'     => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'title' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_category']['title'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
    ],
];
