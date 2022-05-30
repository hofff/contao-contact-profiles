<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Controller;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\ServiceAnnotation\Page;
use Contao\PageModel;
use Contao\PageRegular;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Page(type="contact_profile", path="{alias}", requirements={"alias": ".+"})
 */
final class ContactProfilePageController
{
    private ProfileRepository $profiles;

    public function __construct(ProfileRepository $profiles)
    {
        $this->profiles = $profiles;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function __invoke(string $alias, PageModel $pageModel, Request $request): Response
    {
        $request->attributes->set(Profile::class, $this->fetchProfile($alias));

        // The legacy framework relies on the global $objPage variable
        $GLOBALS['objPage'] = $pageModel;

        return (new PageRegular())->getResponse($pageModel, true);
    }

    private function fetchProfile(string $alias): Profile
    {
        $profile = $this->profiles->fetchPublishedByIdOrAlias($alias);
        if ($profile === null) {
            throw new PageNotFoundException('Contact profile not found');
        }

        return $profile;
    }
}
