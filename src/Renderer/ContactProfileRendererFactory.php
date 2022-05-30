<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Contao\Model;
use Contao\StringUtil;
use Hofff\Contao\Consent\Bridge\ConsentId\ConsentIdParser;
use Hofff\Contao\Consent\Bridge\Exception\InvalidArgumentException;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactProfileRendererFactory
{
    private TranslatorInterface $translator;

    private ConsentIdParser $consentIdParser;

    private FieldRenderer $fieldRenderer;

    private ContactProfileUrlGenerator $urlGenerator;

    private RequestScopeMatcher $scopeMatcher;

    public function __construct(
        TranslatorInterface $translator,
        ConsentIdParser $consentIdParser,
        FieldRenderer $fieldRenderer,
        ContactProfileUrlGenerator $urlGenerator,
        RequestScopeMatcher $scopeMatcher
    ) {
        $this->translator      = $translator;
        $this->consentIdParser = $consentIdParser;
        $this->fieldRenderer   = $fieldRenderer;
        $this->urlGenerator    = $urlGenerator;
        $this->scopeMatcher    = $scopeMatcher;
    }

    public function create(Model $model): ContactProfileRenderer
    {
        $moreLabel = (string) $model->hofff_contact_more ?: $this->translator->trans('MSC.more', [], 'contao_default');
        $renderer  = (new ContactProfileRenderer($this->fieldRenderer, $moreLabel, $this->urlGenerator))
            ->withFields(StringUtil::deserialize($model->hofff_contact_fields, true));

        if ($this->scopeMatcher->isFrontendRequest() && $model->hofff_contact_template) {
            $renderer->withTemplate($model->hofff_contact_template);
        }

        $size = StringUtil::deserialize($model->size, true);
        if ($size) {
            $renderer->withImageSize($size);
        }

        $this->addConsentId($model, $renderer, 'youtube');
        $this->addConsentId($model, $renderer, 'vimeo');

        return $renderer;
    }

    protected function addConsentId(Model $model, ContactProfileRenderer $renderer, string $type): void
    {
        $key = 'hofff_contact_consent_tag_' . $type;

        if (! $model->{$key}) {
            return;
        }

        try {
            $renderer->withConsentId($type, $this->consentIdParser->parse($model->{$key}));
        } catch (InvalidArgumentException $exception) {
            // Do nothing. Probably consent id isn't supported anymore
        }
    }
}
