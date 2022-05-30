<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\SocialAccount;

use Contao\Model;

/**
 * @property numeric-string|int $id
 * @property string             $name
 */
abstract class SocialAccount extends Model
{
    /** @var string */
    protected static $strTable = 'tl_contact_social_account';

    abstract public function socialAccountId(): int;
}
