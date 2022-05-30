<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\Model;
use Contao\Model\Collection;
use Terminal42\DcMultilingualBundle\Model\Multilingual;

final class NewsCategoryProfilesListener extends SourceListener
{
    protected function source(): string
    {
        return 'news_categories';
    }

    protected function fetchSource(string $alias): ?Model
    {
        $repository = $this->repositoryManager->getRepository(NewsCategoryModel::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->findPublishedByIdOrAlias($alias);
    }

    protected function fetchProfiles(Model $sourceModel): ?Collection
    {
        $sourceId = $sourceModel instanceof Multilingual ? $sourceModel->getLanguageId() : $sourceModel->id;

        return $this->repository->findByNewsCategory((int) $sourceId);
    }
}
