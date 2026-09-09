<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\InsertTag\InsertTagParser;
use Contao\CoreBundle\Routing\ResponseContext\HtmlHeadBag\HtmlHeadBag;
use Contao\CoreBundle\Routing\ResponseContext\ResponseContextAccessor;
use Contao\CoreBundle\String\HtmlDecoder;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\ProfileDetailPageEvent;
use Hofff\Contao\ContactProfiles\SocialTags\SocialTagsGenerator;

use function str_replace;
use function strip_tags;
use function trim;

final class DetailPageListener
{
    public function __construct(
        private readonly InsertTagParser $insertTagParser,
        private readonly SocialTagsGenerator $socialTagsGenerator,
        private readonly ResponseContextAccessor $contextAccessor,
        private readonly HtmlDecoder $htmlDecoder,
    ) {
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

        $context = $this->contextAccessor->getResponseContext();
        if ($context && $context->has(HtmlHeadBag::class)) {
            $bag = $context->get(HtmlHeadBag::class);

            $bag->setTitle($GLOBALS['objPage']->pageTitle);
            $bag->setMetaDescription($this->htmlDecoder->inputEncodedToPlainText($GLOBALS['objPage']->description));
        }

        $this->socialTagsGenerator->generate($profile);
    }

    private function prepareMetaDescription(string $text): string
    {
        $text = $this->insertTagParser->replaceInline($text);
        $text = strip_tags($text);
        $text = str_replace("\n", ' ', $text);
        $text = StringUtil::substr($text, 320);

        return trim($text);
    }
}
