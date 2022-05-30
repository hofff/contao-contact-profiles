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
 * @property string             $teaser
 * @property string             $websiteTitle
 * @property string             $caption
 * @property string|null        $galleryOrder
 */
abstract class Profile extends Model
{
    /** @var string */
    protected static $strTable = 'tl_contact_profile';

    abstract public function profileId(): int;
}
