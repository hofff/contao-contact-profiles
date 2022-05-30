<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_fill_keys;
use function iconv;
use function range;

final class ContactProfileInitialsFilterController extends AbstractHybridController
{
    private ProfileRepository $profiles;

    private EventDispatcherInterface $eventDispatcher;

    /** @param Adapter<Input> $inputAdapter */
    public function __construct(
        TemplateRenderer $templateRenderer,
        RequestScopeMatcher $scopeMatcher,
        ResponseTagger $responseTagger,
        RouterInterface $router,
        TranslatorInterface $translator,
        TokenChecker $tokenChecker,
        ProfileRepository $profiles,
        EventDispatcherInterface $eventDispatcher,
        Adapter $inputAdapter
    ) {
        parent::__construct(
            $templateRenderer,
            $scopeMatcher,
            $responseTagger,
            $router,
            $translator,
            $tokenChecker,
            $inputAdapter
        );

        $this->profiles        = $profiles;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        /** @psalm-suppress PossiblyNullReference - Input adapter is always set */
        $data['activeLetter'] = (string) $this->inputAdapter->get('auto_item');
        $data['letters']      = $this->calculateLettersUsage($model);
        $data['resetUrl']     = $GLOBALS['objPage']->getFrontendUrl();
        $data['filterUrl']    = static function (string $letter): string {
            return $GLOBALS['objPage']->getFrontendUrl('/' . $letter);
        };

        return $data;
    }

    /**
     * @return array<string,int>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function calculateLettersUsage(Model $model): array
    {
        $letters = array_fill_keys(range('a', 'z'), 0);
        $special = 0;

        switch ($model->hofff_contact_source) {
            case 'dynamic':
                $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
                $event   = new LoadContactProfilesEvent($model, $GLOBALS['objPage'], $sources);
                $this->eventDispatcher->dispatch($event, $event::NAME);

                foreach ($event->profiles() as $profile) {
                    $letter = iconv('UTF-8', 'ASCII//TRANSLIT', $profile->lastname[0] ?? '');
                    if (isset($letters[$letter])) {
                        $letters[$letter] ++;
                    } else {
                        $special++;
                    }
                }

                break;

            case 'categories':
                $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);

                foreach ($this->profiles->fetchInitialsOfPublishedByCategories($categoryIds) as $letter) {
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
                $profileIds = StringUtil::deserialize($model->hofff_contact_profiles, true);

                foreach ($this->profiles->fetchInitialsOfPublishedByProfileIds($profileIds) as $letter) {
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
}
