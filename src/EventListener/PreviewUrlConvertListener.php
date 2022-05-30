<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Symfony\Component\HttpFoundation\Request;

final class PreviewUrlConvertListener
{
    private ContaoFramework $framework;

    private ProfileRepository $contactProfiles;

    private ContactProfileUrlGenerator $urlGenerator;

    public function __construct(
        ContaoFramework $framework,
        ProfileRepository $contactProfiles,
        ContactProfileUrlGenerator $urlGenerator
    ) {
        $this->framework       = $framework;
        $this->contactProfiles = $contactProfiles;
        $this->urlGenerator    = $urlGenerator;
    }

    /**
     * Adds the front end preview URL to the event.
     */
    public function __invoke(PreviewUrlConvertEvent $event): void
    {
        if (! $this->framework->isInitialized()) {
            return;
        }

        $request = $event->getRequest();
        $options = [];
        if ($request->query->has('locale')) {
            $options['language'] = $request->query->get('locale');
        }

        $contactProfile = $this->getContactProfile($request, $options);
        if (! $contactProfile instanceof Profile) {
            return;
        }

        $url = $this->urlGenerator->generateDetailUrl(
            $contactProfile,
            ContactProfileUrlGenerator::PREVIEW_URL,
            $options
        );

        if ($url === null) {
            return;
        }

        $event->setUrl($url);
    }

    /** @param array<string,mixed> $options */
    private function getContactProfile(Request $request, array $options): ?Profile
    {
        if (! $request->query->has('hofff_contact_profile')) {
            return null;
        }

        return $this->contactProfiles->findOneBy(
            ['.id=?'],
            [$request->query->getInt('hofff_contact_profile')],
            $options
        );
    }
}
