<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Model;

final class EventsContactProfilesListener extends SourceListener
{
    protected function source(): string
    {
        return 'event';
    }

    protected function fetchSource(string $alias): ?Model
    {
        $newsArchive = $this->getCalendar();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->repositoryManager->getRepository(CalendarEventsModel::class);

        return $repository->findPublishedByParentAndIdOrAlias($alias, [$newsArchive->id]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getCalendar(): ?CalendarModel
    {
        $repository = $this->repositoryManager->getRepository(CalendarModel::class);

        return $repository->findOneByJumpTo($GLOBALS['objPage']->id);
    }
}
