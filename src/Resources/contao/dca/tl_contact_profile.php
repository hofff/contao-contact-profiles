<?php

declare(strict_types=1);

use Contao\Config;
use Hofff\Contao\ContactProfiles\EventListener\Dca\AccountTypeOptions;
use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileDcaListener;

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
                ContactProfileDcaListener::class,
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
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                    . '\')) return false; Backend.getScrollOffset();"',
            ],
            'toggle' => [
                'label'           => &$GLOBALS['TL_LANG']['tl_contact_profile']['toggle'],
                'icon'            => 'visible.svg',
                'attributes'      => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
                'button_callback' => [ContactProfileDcaListener::class, 'toggleIcon'],
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
        'default' => '{personal_legend},salutation,title,firstname,lastname,alias,position,profession,image,caption'
            . ';{contact_legend},phone,mobile,fax,email,website,websiteTitle,accounts'
            . ';{details_legend},teaser,description,statement,responsibilities'
            . ';{gallery_legend:hide},gallery'
            . ';{videos_legend:hide},videos'
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
        'pid'              => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'tstamp'           => ['sql' => "int(10) unsigned NOT NULL default '0'"],
        'alias'            => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['alias'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => false, 'maxlength' => 255, 'tl_class' => 'w50'],
            'sql'       => 'varchar(255) BINARY NOT NULL default \'\'',
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
            'eval'      => ['maxlength' => 255, 'tl_class' => 'clr w50', 'profileField' => true],
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
                'extensions'   => Config::get('validImageTypes'),
                'profileField' => true,
            ],
            'sql'       => 'binary(16) NULL',
        ],
        'caption'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['caption'],
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => false],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
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
        'website'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['website'],
            'exclude'   => true,
            'search'    => true,
            'inputType' => 'text',
            'eval'      => [
                'maxlength'      => 255,
                'rgxp'           => 'url',
                'pagePicker'     => true,
                'decodeEntities' => true,
                'tl_class'       => 'w50',
                'profileField'   => true,
                'dcaPicker'      => true,
            ],
            'sql'       => 'varchar(255) NOT NULL default \'\'',
        ],
        'websiteTitle'     => [
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'w50', 'profileField' => false],
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
                            AccountTypeOptions::class,
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
            'sql'       => 'blob NULL',
        ],
        'teaser'           => [
            'label'       => &$GLOBALS['TL_LANG']['tl_contact_profile']['teaser'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true, 'profileField' => true],
            'explanation' => 'insertTags',
            'sql'         => 'mediumtext NULL',
        ],
        'description'      => [
            'label'       => &$GLOBALS['TL_LANG']['tl_contact_profile']['description'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true, 'profileField' => true],
            'explanation' => 'insertTags',
            'sql'         => 'mediumtext NULL',
        ],
        'responsibilities' => [
            'label'      => &$GLOBALS['TL_LANG']['tl_contact_profile']['responsibilities'],
            'exclude'    => true,
            'inputType'  => 'checkboxWizard',
            'foreignKey' => 'tl_contact_responsibility.name',
            'eval'       => ['multiple' => true, 'profileField' => true],
            'sql'        => 'mediumblob NULL',
        ],
        'statement'        => [
            'label'       => &$GLOBALS['TL_LANG']['tl_contact_profile']['statement'],
            'exclude'     => true,
            'search'      => true,
            'inputType'   => 'textarea',
            'eval'        => ['mandatory' => false, 'rte' => 'tinyMCE', 'helpwizard' => true, 'profileField' => true],
            'explanation' => 'insertTags',
            'sql'         => 'mediumtext NULL',
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
        'videos'           => [
            'label'         => &$GLOBALS['TL_LANG']['tl_contact_profile']['videos'],
            'exclude'       => true,
            'inputType'     => 'multiColumnWizard',
            'save_callback' => [
                [ContactProfileDcaListener::class, 'saveVideos'],
            ],
            'eval'          => [
                'tl_class'     => 'clr',
                'profileField' => true,
                'columnFields' => [
                    'videoTitle'  => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['videoTitle'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'mandatory' => false,
                            'maxlength' => 255,
                            'tl_class'  => '',
                            'style'     => 'width: 100%',
                        ],
                        'sql'       => 'varchar(255) NOT NULL default \'\'',
                    ],
                    'videoSource' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['videoSource'],
                        'inputType' => 'select',
                        'options'   => ['local', 'youtube', 'vimeo'],
                        'eval'      => [
                            'includeBlankOption' => true,
                            'style'              => 'width: 100%',
                        ],
                    ],
                    'video'       => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['videoVideo'],
                        'inputType' => 'text',
                        'eval'      => [
                            'maxlength'      => 128,
                            'rgxp'           => 'url',
                            'tl_class'       => 'wizard',
                            'decodeEntities' => true,
                            'dcaPicker'      => [
                                'do'        => 'files',
                                'context'   => 'file',
                                'icon'      => 'pickfile.svg',
                                'fieldType' => 'radio',
                                'filesOnly' => true,
                            ],
                            'style'          => 'width: 100%',
                        ],
                    ],
                    'image'       => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['image'],
                        'exclude'   => true,
                        'inputType' => 'fileTree',
                        'eval'      => [
                            'filesOnly'  => true,
                            'fieldType'  => 'radio',
                            'mandatory'  => false,
                            'extensions' => Config::get('validImageTypes'),
                        ],
                    ],
                    'aspect'      => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['videoAspect'],
                        'exclude'   => true,
                        'inputType' => 'select',
                        'options'   => ['16:9', '16:10', '21:9', '4:3', '3:2'],
                        'reference' => &$GLOBALS['TL_LANG']['tl_contact_profile']['videoAspect'],
                        'eval'      => ['includeBlankOption' => true, 'nospace' => true, 'tl_class' => 'w50'],
                    ],
                ],
            ],
            'sql'           => [
                'type'    => 'blob',
                'notnull' => false,
                'default' => null,
            ],
        ],
        'gallery'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_contact_profile']['gallery'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => [
                'profileField' => true,
                'multiple'     => true,
                'fieldType'    => 'checkbox',
                'orderField'   => 'galleryOrder',
                'files'        => true,
                'isGallery'    => true,
                'extensions'   => Config::get('validImageTypes'),
            ],
            'sql'       => [
                'type'    => 'blob',
                'notnull' => false,
                'default' => null,
            ],
        ],
        'galleryOrder'     => [
            'label' => &$GLOBALS['TL_LANG']['MSC']['sortOrder'],
            'sql'   => [
                'type'    => 'blob',
                'notnull' => false,
                'default' => null,
            ],
        ],
    ],
];
