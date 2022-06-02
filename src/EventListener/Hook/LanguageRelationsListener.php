<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

use function strip_tags;
use function trim;

/** @Hook("hofff_language_relations_language_switcher") */
final class LanguageRelationsListener
{
    private ProfileRepository $profiles;

    private ContactProfileUrlGenerator $urlGenerator;

    private RequestStack $requestStack;

    public function __construct(
        ProfileRepository $profiles,
        ContactProfileUrlGenerator $urlGenerator,
        RequestStack $requestStack
    ) {
        $this->profiles     = $profiles;
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    /**
     * @param array<int,array<string,mixed>> $items
     *
     * @return array<int,array<string,mixed>>
     */
    public function __invoke(array $items): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return $items;
        }

        $profile = $request->attributes->get(Profile::class);
        if (! $profile instanceof Profile) {
            return $items;
        }

        foreach ($items as $rootId => $item) {
            $language   = $item['model']->language;
            $detailPage = $this->urlGenerator->getDetailPage($profile, ['language' => $language]);

            if ($detailPage === null || (int) $detailPage->hofff_root_page_id !== $rootId) {
                continue;
            }

            $translatedProfile = $this->profiles->findOneBy(
                ['.id=?'],
                [$profile->profileId()],
                ['language' => $language]
            );

            if (! $translatedProfile) {
                continue;
            }

            $items[$rootId]['href']      = $this->urlGenerator->generateUrlWithPage($translatedProfile, $detailPage);
            $items[$rootId]['pageTitle'] = strip_tags(trim($profile->firstname . ' ' . $profile->lastname));
        }

        return $items;
    }
}
