<?php

declare(strict_types=1);

// Backend module
use Hofff\Contao\ContactProfiles\Frontend\NewsCategories\RelatedNewsCategoriesModule;

$GLOBALS['BE_MOD']['content']['hofff_contact_profiles'] = [
    'tables'     => [
        'tl_contact_category',
        'tl_contact_profile',
        'tl_contact_responsibility',
        'tl_contact_social_account',
    ],
    'stylesheet' => ['bundles/hofffcontaocontactprofiles/css/background.css'],
];

// Frontend modules
$GLOBALS['FE_MOD']['hofff_contact_profiles']['hofff_contact_profile_related_categories']
    = RelatedNewsCategoriesModule::class;
