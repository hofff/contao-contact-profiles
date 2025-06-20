<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\Category\Category;
use Hofff\Contao\ContactProfiles\Model\Category\CategoryRepository;

use function assert;

#[AsCallback('tl_content', 'fields.hofff_contact_categories.options')]
#[AsCallback('tl_module', 'fields.hofff_contact_categories.options')]
final class CategoryOptions
{
    public function __construct(private readonly CategoryRepository $categories)
    {
    }

    /** @return array<int,string> */
    public function __invoke(): array
    {
        $collection = $this->categories->findAll();
        $options    = [];

        if (! $collection instanceof Collection) {
            return $options;
        }

        foreach ($collection as $category) {
            assert($category instanceof Category);
            $options[$category->categoryId()] = $category->title;
        }

        return $options;
    }
}
