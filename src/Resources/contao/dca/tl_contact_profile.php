<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_contact_profile'] = [

    // Config
    'config'   => [
        'dataContainer'    => 'Table',
        'ptable'           => 'tl_contact_category',
        'enableVersioning' => true,
        'sql'              => [
            'keys' => [
                'id'        => 'primary',
                'published' => 'index',
            ],
        ],
    ],

    // List
    'list'     => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['lastname', 'firstname'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => [
                \Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileDcaListener::class,
                'generateRow',
            ],
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
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_contact_profile']['toggle'],
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [\Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileDcaListener::class, 'toggleIcon'],
                'showInHeader'    => true,
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
        'default' => '{personal_legend},salutation,title,firstname,lastname,position,profession,image'
            . ';{contact_legend},phone,mobile,fax,email,accounts'
            . ';{details_legend},teaser,description,responsibilities'
            . ';{redirect_legend},jumpTo'
            . ';{published_legend},published',
    ],

    // Fields
    'fields'   => [
        'id'               => [
            'label'  => ['ID'],
            'search' => true,
            'sql'    => 'int(10) unsigned NOT NULL auto_increment',
        ],
        'pid'              => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'tstamp'           => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'salutation'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['salutation'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => 'varchar(32) NOT NULL default \'\'',

        ],
        'title'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['title'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'firstname'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['firstname'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'lastname'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['lastname'],
            'flag'      => 1,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'position'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['position'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'profession'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['profession'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => true],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'image'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['image'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'filesOnly'    => true,
                'fieldType'    => 'radio',
                'mandatory'    => false,
                'tl_class'     => 'clr',
                'extensions'   => Contao\Config::get('validImageTypes'),
                'profileField' => true,
            ],
            'sql'       => 'binary(16) NULL',
        ],
        'phone'            => [
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
        'mobile'           => [
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
        'fax'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['fax'],
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
        'email'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['email'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength'      => 255,
                'rgxp'           => 'email',
                'decodeEntities' => true,
                'tl_class'       => 'w50',
                'profileField'   => true,
            ],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'accounts'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['accounts'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'tl_class'     => 'clr',
                'profileField' => true,
                'columnFields' => [
                    'type' => [
                        'label'            => &$GLOBALS['TL_LANG']['tl_contact_profile']['accountType'],
                        'inputType'        => 'select',
                        'options_callback' => [
                            \Hofff\Contao\ContactProfiles\EventListener\Dca\AccountTypeOptions::class,
                            '__invoke',
                        ],
                        'eval'             => [
                            'includeBlankOption' => true,
                            'tl_class'           => 'w50',
                            'style'              => 'width: 100%',
                        ],
                    ],
                    'url'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['url'],
                        'inputType' => 'text',
                        'eval'      => [
                            'maxlength' => 128,
                            'rgxp'      => 'url',
                            'tl_class'  => 'w50',
                        ],
                    ],
                ],
            ],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'teaser'           => [
            'label'       => &$GLOBALS['TL_LANG']['tl_contact_profile']['teaser'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true, 'profileField' => true],
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ],
        'description'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_contact_profile']['description'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true, 'profileField' => true],
            'explanation' => 'insertTags',
            'sql'         => "mediumtext NULL",
        ],
        'responsibilities' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_contact_profile']['responsibilities'],
            'exclude'    => true,
            'inputType'  => 'checkboxWizard',
            'foreignKey' => 'tl_contact_responsibility.name',
            'eval'       => ['multiple' => true, 'profileField' => true],
            'sql'        => "mediumblob NULL",
        ],
        'jumpTo'           => [
            'label'      => &$GLOBALS['TL_LANG']['tl_contact_profile']['jumpTo'],
            'exclude'    => true,
            'inputType'  => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval'       => ['fieldType' => 'radio', 'profileField' => true],
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'published'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['published'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'checkbox',
            'eval'      => ['doNotCopy' => true],
            'sql'       => "char(1) NOT NULL default ''",
        ],
    ],
];
