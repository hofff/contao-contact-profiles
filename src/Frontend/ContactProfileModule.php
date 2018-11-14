<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Module;

final class ContactProfileModule extends Module
{
    use ContactProfileTrait;

    protected $strTemplate = 'mod_hofff_contact_profile';
}
