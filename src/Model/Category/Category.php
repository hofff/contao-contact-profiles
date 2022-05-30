<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Contao\Model;

/**
 * @property numeric-string|int $id
 * @property string             $title
 * @property numeric-string|int $jumpTo
 */
abstract class Category extends Model
{
    /** @var string */
    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingNativeTypeHint
    protected static $strTable = 'tl_contact_category';

    abstract public function categoryId(): int;
}
