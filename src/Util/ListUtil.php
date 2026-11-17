<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Util;

use function array_map;
use function array_values;
use function intval;
use function strval;

final class ListUtil
{
    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<string>
     */
    public static function toStringList(array $data): array
    {
        return array_values(array_map(strval(...), $data));
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<int>
     */
    public static function toIntList(array $data): array
    {
        return array_values(array_map(intval(...), $data));
    }
}
