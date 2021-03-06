<?php

declare(strict_types=1);

use Hofff\Contao\ContactProfiles\EventListener\Hook\AddContactProfileInformationListener;
use Hofff\Contao\ContactProfiles\Frontend\ContactProfileElement;

// Backend module
$GLOBALS['BE_MOD']['content']['hofff_contact_profiles'] = [
    'tables' => [
        'tl_contact_category',
        'tl_contact_profile',
        'tl_contact_responsibility',
        'tl_contact_social_account',
    ],
    'stylesheet' => ['bundles/hofffcontaocontactprofiles/css/background.css'],
];

// Content element
$GLOBALS['TL_CTE']['includes']['hofff_contact_profile'] = ContactProfileElement::class;

// Frontend module
$GLOBALS['FE_MOD']['miscellaneous']['hofff_contact_profile'] = ContactProfileElement::class;

// Hooks
$GLOBALS['TL_HOOKS']['parseTemplate'][] = [AddContactProfileInformationListener::class, 'onParseTemplate'];
