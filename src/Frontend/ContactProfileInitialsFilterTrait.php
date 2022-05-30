<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_fill_keys;
use function iconv;
use function range;
use function substr;

trait ContactProfileInitialsFilterTrait
{
    public function generate(): string
    {
        /** @psalm-var RequestStack $requestStack */
        $requestStack = System::getContainer()->get('request_stack');
        /** @psalm-var ScopeMatcher $scopeMatcher */
        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
        $request      = $requestStack->getCurrentRequest();

        if ($request && $scopeMatcher->isBackendRequest($request)) {
            return $this->renderBackendWildcard();
        }

        return parent::generate();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function compile(): void
    {
        $this->Template->letters      = $this->calculateLettersUsage();
        $this->Template->activeLetter = (string) Input::get('auto_item');
        $this->Template->resetUrl     = $GLOBALS['objPage']->getFrontendUrl();
        $this->Template->filterUrl    = static function (string $letter): string {
            return $GLOBALS['objPage']->getFrontendUrl('/' . $letter);
        };
    }

    /**
     * @return array<string,int>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function calculateLettersUsage(): array
    {
        $letters = array_fill_keys(range('a', 'z'), 0);
        $special = 0;
        /** @psalm-var ProfileRepository $repository */
        $repository = System::getContainer()->get(ProfileRepository::class);

        switch ($this->hofff_contact_source) {
            case 'dynamic':
                /** @psalm-var EventDispatcherInterface $eventDispatcher */
                $eventDispatcher = System::getContainer()->get('event_dispatcher');
                $sources         = StringUtil::deserialize($this->hofff_contact_sources, true);
                $event           = new LoadContactProfilesEvent($this, $GLOBALS['objPage'], $sources);
                $eventDispatcher->dispatch($event, $event::NAME);

                foreach ($event->profiles() as $profile) {
                    $letter = iconv('UTF-8', 'ASCII//TRANSLIT', substr($profile->lastname, 0, 1));
                    if (isset($letters[$letter])) {
                        $letters[$letter] ++;
                    } else {
                        $special++;
                    }
                }

                break;

            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                foreach ($repository->fetchInitialsOfPublishedByCategories($categoryIds) as $letter) {
                    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $letter['letter']);
                    if (isset($letters[$normalized])) {
                        $letters[$normalized] = $letter['count'];
                    } else {
                        $special += $letter['count'];
                    }
                }

                break;

            case 'custom':
            default:
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                foreach ($repository->fetchInitialsOfPublishedByProfileIds($profileIds) as $letter) {
                    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $letter['letter']);
                    if (isset($letters[$normalized])) {
                        $letters[$normalized] = $letter['count'];
                    } else {
                        $special += $letter['count'];
                    }
                }
        }

        if ($special > 0) {
            $letters['numeric'] = $special;
        }

        return $letters;
    }

    abstract protected function renderBackendWildcard(): string;
}
