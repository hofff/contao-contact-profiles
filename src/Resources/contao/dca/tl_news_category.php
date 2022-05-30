<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Dca\ContactProfileOptions;

$GLOBALS['TL_DCA']['tl_news_category']['fields']['hofff_contact_profiles'] = [
    'exclude'          => true,
    'inputType'        => 'picker',
    'options_callback' => [ContactProfileOptions::class, '__invoke'],
    'eval'             => [
        'tl_class'   => 'clr long',
        'multiple'   => true,
        'chosen'     => true,
    ],
    'relation'         => [
        'type'  => 'haste-ManyToMany',
        'table' => 'tl_contact_profile',
    ],
];
