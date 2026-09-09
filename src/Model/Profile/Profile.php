<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Contao\Model;

/**
 * @property numeric-string|int $id
 * @property numeric-string|int $pid
 * @property numeric-string|int $jumpTo
 * @property string             $alias
 * @property string|null        $image
 * @property string             $firstname
 * @property string             $lastname
 * @property string|null        $teaser
 * @property string|null        $description
 * @property string             $websiteTitle
 * @property string             $caption
 * @property string|null        $galleryOrder
 * @property string|null        $accounts
 */
abstract class Profile extends Model
{
    /** @var string */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    protected static $strTable = 'tl_contact_profile';

    abstract public function profileId(): int;
}
