<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Input;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;

final class EventsContactProfilesListener
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    /** @var PublishedContactProfilesQuery */
    private $query;

    public function __construct(ContaoFrameworkInterface $framework, PublishedContactProfilesQuery $query)
    {
        $this->framework = $framework;
        $this->query     = $query;
    }

    public function onLoadContactProfiles(LoadContactProfilesEvent $event) : void
    {
        $calendarEvent = $this->getEvent();
        if (! $calendarEvent) {
            return;
        }

        $profileIds = StringUtil::deserialize($calendarEvent->hofff_contact_profiles, true);
        $profiles   = ($this->query)($profileIds);

        $event->setProfiles($profiles);
    }

    private function getEvent() : ?CalendarEventsModel
    {
        $eventAlias = $this->getEventAlias();
        if (! $eventAlias) {
            return null;
        }

        $newsArchive = $this->getCalendar();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->framework->getAdapter(CalendarEventsModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$eventAlias, [$newsArchive->id]]);
    }

    private function getEventAlias() : ?string
    {
        if (! isset($GLOBALS['objPage'])) {
            return null;
        }

        $inputAdapter  = $this->framework->getAdapter(Input::class);
        $configAdapter = $this->framework->getAdapter(Config::class);

        if ($configAdapter->__call('get', ['useAutoItem'])) {
            return $inputAdapter->__call('get', ['auto_item']);
        }

        return $inputAdapter->__call('get', ['items']);
    }

    private function getCalendar() : ?CalendarModel
    {
        $repository = $this->framework->getAdapter(CalendarModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
