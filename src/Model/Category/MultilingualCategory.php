<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;

final class MultilingualCategory extends Category
{
    use MultilingualTrait;

    public function categoryId(): int
    {
        return (int) $this->getLanguageId();
    }
}
