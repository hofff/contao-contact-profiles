<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Util;

use function array_filter;
use function array_values;

final class ListUtil
{
    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<string>
     */
    public static function toStringList(array $data): array
    {
        return array_values(array_filter($data, 'strval'));
    }

    /**
     * @param array<array-key, mixed> $data
     *
     * @return list<int>
     */
    public static function toIntList(array $data): array
    {
        return array_values(array_filter($data, 'intval'));
    }
}
