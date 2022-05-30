<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\Category\CategoryRepository;

/**
 * @Callback(table="tl_content", target="fields.hofff_contact_categories.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_categories.options")
 */
final class CategoryOptions
{
    private CategoryRepository $categories;

    public function __construct(CategoryRepository $categories)
    {
        $this->categories = $categories;
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
            $options[$category->categoryId()] = $category->title;
        }

        return $options;
    }
}
