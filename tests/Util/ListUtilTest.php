<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Util;

use Hofff\Contao\ContactProfiles\Util\ListUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(ListUtil::class)]
final class ListUtilTest extends TestCase
{
    /** @return iterable<string, array{array<array-key, mixed>, list<string>}> */
    public static function stringListProvider(): iterable
    {
        yield 'empty list' => [[], []];
        yield 'image size with size id keeps positions' => [['', '', '55'], ['', '', '55']];
        yield 'image size with dimensions' => [['180', '180', 'crop'], ['180', '180', 'crop']];
        yield 'empty values are kept' => [['', '', ''], ['', '', '']];
        yield 'zero is kept' => [['0', 0], ['0', '0']];
        yield 'scalars are cast to string' => [[180, 1.5, true, null], ['180', '1.5', '1', '']];
        yield 'keys are reindexed' => [['a' => 'news', 5 => 'faq'], ['news', 'faq']];
    }

    /**
     * @param array<array-key, mixed> $input
     * @param list<string>            $expected
     */
    #[DataProvider('stringListProvider')]
    public function testToStringList(array $input, array $expected): void
    {
        self::assertSame($expected, ListUtil::toStringList($input));
    }

    /** @return iterable<string, array{array<array-key, mixed>, list<int>}> */
    public static function intListProvider(): iterable
    {
        yield 'empty list' => [[], []];
        yield 'numeric strings are cast to int' => [['3', '1', '2'], [3, 1, 2]];
        yield 'empty values are kept as zero' => [['', '0', '5'], [0, 0, 5]];
        yield 'ints are kept' => [[4, 2], [4, 2]];
        yield 'keys are reindexed' => [[3 => '7', 'x' => '9'], [7, 9]];
    }

    /**
     * @param array<array-key, mixed> $input
     * @param list<int>               $expected
     */
    #[DataProvider('intListProvider')]
    public function testToIntList(array $input, array $expected): void
    {
        self::assertSame($expected, ListUtil::toIntList($input));
    }
}
