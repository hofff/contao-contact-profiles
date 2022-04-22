<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\Consent\Bridge\ConsentId\ConsentIdParser;
use Hofff\Contao\Consent\Bridge\Exception\InvalidArgumentException;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

use function defined;

trait CreateRendererTrait
{
    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function createRenderer(): ContactProfileRenderer
    {
        /** @psalm-var FieldRenderer $fieldRenderer */
        $fieldRenderer = System::getContainer()->get(FieldRenderer::class);
        /** @psalm-var ContactProfileUrlGenerator $urlGenerator */
        $urlGenerator = System::getContainer()->get(ContactProfileUrlGenerator::class);

        $moreLabel = (string) $this->hofff_contact_more ?: $GLOBALS['TL_LANG']['MSC']['more'];
        $renderer  = (new ContactProfileRenderer($fieldRenderer, $moreLabel, $urlGenerator))
            ->withFields(StringUtil::deserialize($this->hofff_contact_fields, true));

        if (defined('TL_MODE') && TL_MODE === 'FE' && $this->hofff_contact_template) {
            $renderer->withTemplate($this->hofff_contact_template);
        }

        $size = StringUtil::deserialize($this->size, true);
        if ($size) {
            $renderer->withImageSize($size);
        }

        $this->addConsentId($renderer, 'youtube');
        $this->addConsentId($renderer, 'vimeo');

        return $renderer;
    }

    protected function addConsentId(ContactProfileRenderer $renderer, string $type): void
    {
        /** @psalm-var ConsentIdParser $consentIdParser */
        $consentIdParser = System::getContainer()->get(ConsentIdParser::class);
        $key             = 'hofff_contact_consent_tag_' . $type;

        if (! $this->{$key}) {
            return;
        }

        try {
            $renderer->withConsentId($type, $consentIdParser->parse($this->{$key}));
        } catch (InvalidArgumentException $exception) {
            // Do nothing. Probably a not anymore supported consent id
        }
    }
}
