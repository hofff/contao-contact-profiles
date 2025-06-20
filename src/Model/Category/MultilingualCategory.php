<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;
use Override;

final class MultilingualCategory extends Category
{
    use MultilingualTrait;

    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function categoryId(): int
    {
        return (int) $this->getLanguageId();
    }
}
