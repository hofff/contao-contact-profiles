<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

final class MonolingualCategory extends Category
{
    public function categoryId(): int
    {
        return (int) $this->id;
    }
}
