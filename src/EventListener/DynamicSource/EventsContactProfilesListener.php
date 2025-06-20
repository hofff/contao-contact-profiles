<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\DynamicSource;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Model;
use Override;

final class EventsContactProfilesListener extends DynamicSourceListener
{
    #[Override]
    protected function source(): string
    {
        return 'event';
    }

    #[Override]
    protected function fetchSource(string $alias): Model|null
    {
        $newsArchive = $this->getCalendar();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->repositoryManager->getRepository(CalendarEventsModel::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->findPublishedByParentAndIdOrAlias($alias, [$newsArchive->id]);
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function getCalendar(): CalendarModel|null
    {
        $repository = $this->repositoryManager->getRepository(CalendarModel::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->findOneByJumpTo($GLOBALS['objPage']->id);
    }
}
