<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Environment;
use Contao\Input;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use function array_fill_keys;
use function explode;
use function iconv;
use function range;
use function substr;

trait ContactProfileInitialsFilterTrait
{
    public function generate(): string
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->renderBackendWildcard();
        }

        return parent::generate();
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function compile(): void
    {
        $resetUrl = isset($GLOBALS['objPage'])
            ? $GLOBALS['objPage']->getFrontendUrl()
            : explode('?', Environment::get('request'))[0];

        $this->Template->letters      = $this->calculateLettersUsage();
        $this->Template->activeLetter = Input::get('letter');
        $this->Template->resetUrl     = $resetUrl;
        $this->Template->filterUrl    = static function (string $letter) use ($resetUrl) {
            return $resetUrl . '?letter=' . $letter;
        };
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function calculateLettersUsage(): array
    {
        // TODO: Do we need setlocale(LC_CTYPE,"de_DE.UTF-8"); here?

        $letters = array_fill_keys(range('a', 'z'), 0);
        $repository = System::getContainer()->get(ContactProfileRepository::class);

        switch ($this->hofff_contact_source) {
            case 'dynamic':
                $sources = StringUtil::deserialize($this->hofff_contact_sources, true);
                $event = new LoadContactProfilesEvent($this, $GLOBALS['objPage'], $sources);
                System::getContainer()->get('event_dispatcher')->dispatch($event::NAME, $event);

                foreach ($event->profiles() as $profile) {
                    $letter = iconv('UTF-8', 'ASCII//TRANSLIT', substr($profile['lastname'], 0, 1));
                    $letters[$letter] ++;
                }

                break;

            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                foreach ($repository->fetchInitialsOfPublishedByCategories($categoryIds) as $letter) {
                    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $letter['letter']);
                    $letters[$normalized] = $letter['count'];
                }

                break;

            case 'custom':
            default:
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                foreach ($repository->fetchInitialsOfPublishedByProfileIds($profileIds) as $letter) {
                    $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $letter['letter']);
                    $letters[$normalized] = $letter['count'];
                }
        }

        return $letters;
    }

    abstract protected function renderBackendWildcard(): string;
}
