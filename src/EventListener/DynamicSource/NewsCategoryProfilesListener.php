<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\DynamicSource;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Model;
use Contao\Model\Collection;
use Override;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

final class NewsCategoryProfilesListener extends DynamicSourceListener
{
    #[Override]
    protected function source(): string
    {
        return 'news_categories';
    }

    #[Override]
    protected function fetchSource(string $alias): Model|null
    {
        $repository = $this->repositoryManager->getRepository(NewsCategoryModel::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->findPublishedByIdOrAlias($alias);
    }

    #[Override]
    protected function fetchProfiles(Model $sourceModel): Collection|null
    {
        $sourceId = $sourceModel instanceof Multilingual ? $sourceModel->getLanguageId() : $sourceModel->id;

        return $this->repository->findByNewsCategory((int) $sourceId);
    }
}
