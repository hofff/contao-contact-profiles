<?php

declare(strict_types=1);

/**
 * Table tl_contact_responsibility
 */
$GLOBALS['TL_DCA']['tl_contact_responsibility'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'sql'              => [
            'keys' => ['id' => 'primary'],
        ],
    ],

    // List
    'list'     => [
        'sorting'    => [
            'mode'        => 1,
            'fields'      => ['name'],
            'flag'        => 1,
            'panelLayout' => 'search',
        ],
        'label'      => [
            'fields' => ['name'],
            'format' => '%s',
        ],
        'global_operations' => [
            'categories' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'href'  => 'table=',
                'class' => 'header_back',
            ],
        ],
        'operations' => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_responsibility']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contact_responsibility']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_responsibility']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => ['default' => '{name_legend},name;'],

    // Fields
    'fields'   => [
        'id'     => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp' => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'name'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_responsibility']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
    ],
];
