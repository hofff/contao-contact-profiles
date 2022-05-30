<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Responsibility;

use Contao\Model;

/**
 * @property numeric-string|int $id
 */
abstract class Responsibility extends Model
{
    /** @var string */
    protected static $strTable = 'tl_contact_responsibility';

    abstract public function responsibilityId(): int;
}
