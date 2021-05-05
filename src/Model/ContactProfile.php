<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Contao\Model;

/**
 * @property string      $firstname
 * @property string      $lastname
 * @property string      $alias
 * @property string|null image
 */
final class ContactProfile extends Model
{
    /** @var string */
    protected static $strTable = 'tl_contact_profile';
}
