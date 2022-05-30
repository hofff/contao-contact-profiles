<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\Event\PreviewUrlCreateEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

final class PreviewUrlCreateListener
{
    private RequestStack $requestStack;

    private ContaoFramework $framework;

    private ProfileRepository $contactProfiles;

    public function __construct(
        RequestStack $requestStack,
        ContaoFramework $framework,
        ProfileRepository $repository
    ) {
        $this->requestStack    = $requestStack;
        $this->framework       = $framework;
        $this->contactProfiles = $repository;
    }

    /**
     * Adds the news ID to the front end preview URL.
     *
     * @throws RuntimeException
     */
    public function __invoke(PreviewUrlCreateEvent $event): void
    {
        if (! $this->framework->isInitialized() || $event->getKey() !== 'hofff_contact_profiles') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            throw new RuntimeException('The request stack did not contain a request');
        }

        // Return on the contact profile list page
        if ($request->query->get('table') === 'tl_contact_profile' && ! $request->query->has('act')) {
            return;
        }

        $contactProfile = $this->contactProfiles->find($this->getId($event, $request));
        if (! $contactProfile instanceof Profile) {
            return;
        }

        $event->setQuery('hofff_contact_profile=' . $contactProfile->profileId());
    }

    private function getId(PreviewUrlCreateEvent $event, Request $request): int
    {
        // Overwrite the ID if the contact profile settings are edited
        if ($request->query->get('table') === 'tl_contact_profile' && $request->query->get('act') === 'edit') {
            return $request->query->getInt('id');
        }

        return (int) $event->getId();
    }
}
