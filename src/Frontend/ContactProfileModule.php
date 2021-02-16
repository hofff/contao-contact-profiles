<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Module;

final class ContactProfileModule extends Module
{
    use ContactProfileTrait;

    /** @var string */
    protected $strTemplate = 'mod_hofff_contact_profile';

    protected function pageParameter(): string
    {
        return 'm' . $this->id;
    }
}
