<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\Module;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Symfony\Component\HttpFoundation\RequestStack;

use function in_array;

/** @Hook("getAllEvents") */
final class GetAllEventsListener
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * @param array<string, array<int, array<int,array<string,mixed>>>> $events
     * @param array<int,string|int>                                     $calendars
     *
     * @return array<string, array<int, array<int,array<string,mixed>>>>
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke(array $events, array $calendars, int $timeStart, int $timeEnd, Module $module): array
    {
        $profile = $this->getActiveProfile($module);
        if ($profile === null) {
            return $events;
        }

        foreach ($events as $date => $dateEvents) {
            foreach ($dateEvents as $start => $startEvents) {
                foreach ($startEvents as $index => $event) {
                    $contactProfiles = StringUtil::deserialize($event['hofff_contact_profiles'], true);

                    if (in_array($profile->profileId(), $contactProfiles)) {
                        continue;
                    }

                    unset($events[$date][$start][$index]);
                }
            }
        }

        return $events;
    }

    private function getActiveProfile(Module $module): ?Profile
    {
        if (! $module->hofff_contact_related_events) {
            return null;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (! $request) {
            return null;
        }

        $profile = $request->attributes->get(Profile::class);
        if (! $profile instanceof Profile) {
            return null;
        }

        return $profile;
    }
}
