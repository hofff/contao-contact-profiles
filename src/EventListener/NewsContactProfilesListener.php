<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Model;
use Contao\NewsArchiveModel;
use Contao\NewsModel;

final class NewsContactProfilesListener extends SourceListener
{
    protected function source(): string
    {
        return 'news';
    }

    protected function fetchSource(string $alias): ?Model
    {
        $newsArchive = $this->getNewsArchive();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->framework->getAdapter(NewsModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$alias, [$newsArchive->id]]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getNewsArchive(): ?NewsArchiveModel
    {
        $repository = $this->framework->getAdapter(NewsArchiveModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
