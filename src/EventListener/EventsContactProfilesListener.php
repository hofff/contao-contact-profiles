<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CalendarEventsModel;
use Contao\CalendarModel;
use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Util\ContactProfileUtil;

use function in_array;

final class EventsContactProfilesListener
{
    /** @var ContaoFramework */
    private $framework;

    /** @var ContactProfileRepository */
    private $repository;

    public function __construct(ContaoFramework $framework, ContactProfileRepository $repository)
    {
        $this->framework  = $framework;
        $this->repository = $repository;
    }

    public function onLoadContactProfiles(LoadContactProfilesEvent $event): void
    {
        if (! in_array('event', $event->sources(), true)) {
            return;
        }

        $calendarEvent = $this->getEvent();
        if (! $calendarEvent) {
            return;
        }

        $profileIds = StringUtil::deserialize($calendarEvent->hofff_contact_profiles, true);
        $order      = StringUtil::deserialize($calendarEvent->hofff_contact_profiles_order, true);
        $profiles   = $this->repository->fetchPublishedByProfileIds($profileIds);
        $profiles   = ContactProfileUtil::orderListByIds($profiles, $order);

        $event->setProfiles($profiles);
    }

    private function getEvent(): ?CalendarEventsModel
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

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getEventAlias(): ?string
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

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getCalendar(): ?CalendarModel
    {
        $repository = $this->framework->getAdapter(CalendarModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
