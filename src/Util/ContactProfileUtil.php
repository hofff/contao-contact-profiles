<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Util;

use function array_filter;
use function array_flip;
use function array_key_exists;
use function array_map;
use function array_merge;
use function array_values;

final class ContactProfileUtil
{
    /**
     * @param array<int, array<string,mixed>> $profiles
     * @param list<string>                    $orderIds
     *
     * @return array<int, array<string,mixed>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function orderListByIds(array $profiles, array $orderIds): array
    {
        // Remove all values
        $order = array_map(
            /** @param mixed $value */
            static function ($value): void {
            },
            array_flip($orderIds)
        );

        // Move the matching elements to their position in $arrOrder
        foreach ($profiles as $key => $item) {
            if (! array_key_exists($item['id'], $order)) {
                continue;
            }

            $order[$item['id']] = $item;
            unset($profiles[$key]);
        }

        // Remove empty (unreplaced) entries
        $order = array_filter(
            $order,
            static function ($item): bool {
                return $item !== null;
            }
        );

        // Append the left-over images at the end
        return array_merge(array_values($order), array_values($profiles));
    }
}
