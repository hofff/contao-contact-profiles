<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Fixtures;

use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Override;

/**
 * Profile model which records the static query calls instead of querying the database.
 */
final class RecordingProfile extends Profile
{
    /** @var list<array{method: string, column: mixed, value: mixed, options: array<string, mixed>}> */
    public static array $calls = [];

    public static int $count = 0;

    public static function reset(): void
    {
        self::$calls = [];
        self::$count = 0;
    }

    #[Override]
    public function profileId(): int
    {
        return (int) $this->id;
    }

    /**
     * @param mixed                $strColumn
     * @param mixed                $varValue
     * @param array<string, mixed> $arrOptions
     */
    #[Override]
    public static function findBy($strColumn, $varValue, array $arrOptions = []): Collection|null
    {
        self::$calls[] = ['method' => 'findBy', 'column' => $strColumn, 'value' => $varValue, 'options' => $arrOptions];

        return null;
    }

    /**
     * @param mixed                $strColumn
     * @param mixed                $varValue
     * @param array<string, mixed> $arrOptions
     */
    #[Override]
    public static function countBy($strColumn = null, $varValue = null, array $arrOptions = []): int
    {
        self::$calls[] = ['method' => 'countBy', 'column' => $strColumn, 'value' => $varValue, 'options' => $arrOptions];

        return self::$count;
    }
}
