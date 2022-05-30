<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Generator;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;

final class CategoriesProfileProvider extends AbstractProfileProvider
{
    private ProfileRepository $profiles;

    public function __construct(ProfileRepository $profiles)
    {
        $this->profiles = $profiles;
    }

    public function name(): string
    {
        return 'categories';
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function fetchProfiles(Model $model, PageModel $pageModel, ?Specification $specification, int $offset): array
    {
        $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);
        $options     = $this->fetchProfilesOptions($model, $offset);

        if ($specification) {
            $profiles = $this->profiles->fetchPublishedByCategoriesAndSpecification(
                $categoryIds,
                $specification,
                $options
            );
        } else {
            $profiles = $this->profiles->fetchPublishedByCategories($categoryIds, $options);
        }

        /** @psalm-suppress LessSpecificReturnStatement */
        return $profiles ? $profiles->getModels() : [];
    }

    /** {@inheritDoc} */
    public function countTotal(Model $model, array $profiles): int
    {
        $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);

        return $this->profiles->countPublishedByCategories($categoryIds);
    }

    /** {@inheritDoc} */
    protected function fetchInitials(Model $model, PageModel $pageModel): Generator
    {
        $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);

        foreach ($this->profiles->fetchInitialsOfPublishedByCategories($categoryIds) as $row) {
            yield $row['letter'] => (int) $row['count'];
        }
    }
}
