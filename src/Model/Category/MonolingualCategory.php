<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Override;

final class MonolingualCategory extends Category
{
    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function categoryId(): int
    {
        return (int) $this->id;
    }
}
