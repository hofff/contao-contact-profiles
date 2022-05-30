<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Category;

use Netzmacht\Contao\Toolkit\Data\Model\ContaoRepository;

/** @extends ContaoRepository<Category> */
final class CategoryRepository extends ContaoRepository
{
    public function __construct()
    {
        parent::__construct(Category::class);
    }
}
