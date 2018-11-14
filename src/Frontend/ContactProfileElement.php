<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\ContentElement;

final class ContactProfileElement extends ContentElement
{
    use ContactProfileTrait;

    protected $strTemplate = 'ce_hofff_contact_profile';
}
