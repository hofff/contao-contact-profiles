<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Util;

use function array_map;
use function array_unshift;
use function implode;

final class QueryUtil
{
    public static function orderByIds(string $field, array $values): string
    {
        $values = array_map('intval', $values);
        array_unshift($values, ' .' . $field);

        return sprintf('FIELD(%s)', implode(',', $values));
    }
}
