<?php

declare(strict_types=1);

/**
 * Table tl_contact_social_account
 */

use Doctrine\DBAL\Types\Types;

$GLOBALS['TL_DCA']['tl_contact_social_account'] = [

    // Config
    'config'   => [
        'dataContainer' => 'Table',
        'sql'           => [
            'keys' => ['id' => 'primary'],
        ],
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'        => 1,
            'fields'      => ['name'],
            'flag'        => 1,
            'panelLayout' => 'search',
        ],
        'label'             => [
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
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_social_account']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contact_social_account']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '')
                    . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_social_account']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => ['default' => '{name_legend},name,class'],

    // Fields
    'fields'   => [
        'id'               => [
            'label'  => ['ID'],
            'search' => true,
            'sql'       => [
                'type'          => Types::INTEGER,
                'unsigned'      => true,
                'autoincrement' => true,
            ],
        ],
        'tstamp'           => [
            'sql'       => [
                'type'     => Types::INTEGER,
                'unsigned' => true,
                'default'  => 0,
            ],
        ],
        'name'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_social_account']['name'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => [
                'type'    => Types::STRING,
                'length'  => 255,
                'default' => '',
            ],
        ],
        'class'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_social_account']['class'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => [
                'type'    => Types::STRING,
                'length'  => 32,
                'default' => '',
            ],
        ],
    ],
];
