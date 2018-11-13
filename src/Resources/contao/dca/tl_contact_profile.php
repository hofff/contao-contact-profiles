<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_contact_profile'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_contact_category',
        'switchToEdit'     => true,
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'        => 'primary',
                'alias'     => 'index',
                'published' => 'index',
            ],
        ],
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['name'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['tl_contact_profile', 'listContacts'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_profile']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_contact_profile']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_contact_profile']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
        ],
    ],

    // Palettes
    'palettes' => [
        'default' => '{personal_legend},gender,name,job,image'
            . ';{contact_legend},phone,mobile,fax,email,socialMedia'
            . ';{image_legend},shortDescription,description,responsibilities,jumpTo'
            . ';{published_legend},published'
    ],

    // Fields
    'fields'   => [
        'id'         => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'gender'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['gender'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['male', 'female'],
            'reference' => &$GLOBALS['TL_LANG']['MSC'],
            'eval'      => ['mandatory' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => 'varchar(6) NOT NULL default \'\'',
        ],
        'title'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'firstname'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['firstname'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'lastname'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['lastname'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'position'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['position'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'profession' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['profession'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'image'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['image'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'filesOnly'    => true,
                'fieldType'    => 'radio',
                'mandatory'    => true,
                'tl_class'     => 'clr',
                'extensions'   => Config::get('validImageTypes'),
                'profileField' => true,
            ],
            'sql'       => 'binary(16) NULL',
        ],
        'phone'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['phone'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength'      => 64,
                'rgxp'           => 'phone',
                'decodeEntities' => true,
                'tl_class'       => 'w50',
                'profileField'   => true,
            ],
            'sql'       => 'varchar(64) NOT NULL default \'\'',
        ],
        'mobile'     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['mobile'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength'      => 64,
                'rgxp'           => 'phone',
                'decodeEntities' => true,
                'tl_class'       => 'w50',
                'profileField'   => true,
            ],
            'sql'       => 'varchar(64) NOT NULL default \'\'',
        ],
        'fax'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['fax'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength'      => 64,
                'rgxp'           => 'phone',
                'decodeEntities' => true,
                'tl_class'       => 'w50',
                'profileField'   => true
            ],
            'sql'       => 'varchar(64) NOT NULL default \'\'',
        ],
        'email'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['email'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength' => 255,
                'rgxp' => 'email',
                'decodeEntities' => true,
                'tl_class' => 'w50',
                'profileField'   => true
            ],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'published'  => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];
