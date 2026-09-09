<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\DynamicSource;

use Contao\Model;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Override;

final class NewsContactProfilesListener extends DynamicSourceListener
{
    #[Override]
    protected function source(): string
    {
        return 'news';
    }

    #[Override]
    protected function fetchSource(string $alias): Model|null
    {
        $newsArchive = $this->getNewsArchive();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->framework->getAdapter(NewsModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$alias, [$newsArchive->id]]);
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function getNewsArchive(): NewsArchiveModel|null
    {
        $repository = $this->framework->getAdapter(NewsArchiveModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
