<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Module;

/**
 * @property string|null $hofff_contact_more
 * @property string      $hofff_contact_template
 * @property string      $hofff_contact_fields
 * @property string      $hofff_contact_source
 * @property string      $hofff_contact_sources
 * @property string      $hofff_contact_profiles
 * @property string      $hofff_contact_categories
 * @property string      $hofff_contact_profiles_order
 * @property string      $hofff_contact_profiles_order_sql
 * @property string      $size
 * @psalm-suppress PropertyNotSetInConstructor
 */
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
