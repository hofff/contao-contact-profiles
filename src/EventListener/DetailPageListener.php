<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\ProfileDetailPageEvent;
use Hofff\Contao\ContactProfiles\SocialTags\SocialTagsGenerator;

use function str_replace;
use function strip_tags;
use function trim;

final class DetailPageListener
{
    /** @var Adapter<Controller> */
    private Adapter $controllerAdapter;

    private SocialTagsGenerator $socialTagsGenerator;

    /**
     * @param Adapter<Controller> $controllerAdapter
     */
    public function __construct(Adapter $controllerAdapter, SocialTagsGenerator $socialTagsGenerator)
    {
        $this->controllerAdapter   = $controllerAdapter;
        $this->socialTagsGenerator = $socialTagsGenerator;
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function __invoke(ProfileDetailPageEvent $event): void
    {
        if (! isset($GLOBALS['objPage'])) {
            return;
        }

        $profile = $event->profile();

        $GLOBALS['objPage']->pageTitle   = trim($profile->firstname . ' ' . $profile->lastname);
        $GLOBALS['objPage']->description = $this->prepareMetaDescription((string) $profile->teaser);

        $this->socialTagsGenerator->generate($profile);
    }

    private function prepareMetaDescription(string $text): string
    {
        $text = $this->controllerAdapter->replaceInsertTags($text, false);
        $text = strip_tags($text);
        $text = str_replace("\n", ' ', $text);
        $text = StringUtil::substr($text, 320);

        return trim($text);
    }
}
