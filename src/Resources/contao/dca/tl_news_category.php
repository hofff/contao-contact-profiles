<?php

declare(strict_types=1);

$GLOBALS['TL_DCA']['tl_news_category']['fields']['hofff_contact_profiles'] = [
    'exclude'   => true,
    'inputType' => 'picker',
    'eval'      => [
        'tl_class' => 'clr long',
        'multiple' => true,
        'chosen'   => true,
    ],
    'relation'  => [
        'type'  => 'haste-ManyToMany',
        'table' => 'tl_contact_profile',
    ],
];
