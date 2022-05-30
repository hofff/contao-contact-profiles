<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Contao\Model;

/**
 * @property numeric-string|int $id
 * @property string             $title
 */
final class Category extends Model
{
    /** @var string */
    protected static $strTable = 'tl_contact_category';
}
