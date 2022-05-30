<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Hook\AddContactProfileInformationListener;
use Hofff\Contao\ContactProfiles\Frontend\ContactProfileInitialsFilterElement;
use Hofff\Contao\ContactProfiles\Frontend\ContactProfileInitialsFilterModule;

// Backend module
$GLOBALS['BE_MOD']['content']['hofff_contact_profiles'] = [
    'tables'     => [
        'tl_contact_category',
        'tl_contact_profile',
        'tl_contact_responsibility',
        'tl_contact_social_account',
    ],
    'stylesheet' => ['bundles/hofffcontaocontactprofiles/css/background.css'],
];

// Content element
$GLOBALS['TL_CTE']['hofff_contact_profiles']['hofff_contact_profile_initials_filter'] =
    ContactProfileInitialsFilterElement::class;

// Frontend module
$GLOBALS['FE_MOD']['hofff_contact_profiles']['hofff_contact_profile_initials_filter'] =
    ContactProfileInitialsFilterModule::class;

// Hooks
$GLOBALS['TL_HOOKS']['parseTemplate'][] = [AddContactProfileInformationListener::class, 'onParseTemplate'];
