<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Contao\FrontendTemplate;
use Contao\StringUtil;
use Hofff\Contao\Consent\Bridge\ConsentId;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

final class ContactProfileRenderer
{
    private const DEFAULT_TEMPLATE = 'hofff_contact_profile_default';

    private const DEFAULT_FIELD_TEMPLATE = 'hofff_contact_field';

    private FieldRenderer $fieldRenderer;

    /** @var string[] */
    private array $fields = [];

    /** @var list<string>|null */
    private ?array $imageSize = null;

    private string $template = self::DEFAULT_TEMPLATE;

    /** @var string[] */
    private array $fieldTemplates = [];

    private string $defaultFieldTemplate;

    private string $moreLabel;

    /** @var array<string,ConsentId> */
    private array $consentIds = [];

    private ContactProfileUrlGenerator $urlGenerator;

    public function __construct(
        FieldRenderer $fieldRenderer,
        string $moreLabel,
        ContactProfileUrlGenerator $urlGenerator
    ) {
        $this->fieldRenderer        = $fieldRenderer;
        $this->moreLabel            = $moreLabel;
        $this->defaultFieldTemplate = self::DEFAULT_FIELD_TEMPLATE;
        $this->urlGenerator         = $urlGenerator;
    }

    /** @param string[] $fields */
    public function withFields(array $fields): self
    {
        $this->fields = $fields;

        return $this;
    }

    public function withTemplate(string $template): self
    {
        $this->template = $template;

        return $this;
    }

    public function withDefaultFieldTemplate(string $template): self
    {
        $this->defaultFieldTemplate = $template;

        return $this;
    }

    public function defaultFieldTemplate(): string
    {
        return $this->defaultFieldTemplate;
    }

    public function withFieldTemplate(string $field, string $template): self
    {
        $this->fieldTemplates[$field] = $template;

        return $this;
    }

    public function fieldTemplate(string $field, ?string $default = null): ?string
    {
        if (isset($this->fieldTemplates[$field])) {
            return $this->fieldTemplates[$field];
        }

        return $default ?: $this->defaultFieldTemplate;
    }

    /** @param list<string> $imageSize */
    public function withImageSize(array $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function withConsentId(string $type, ConsentId $consentId): self
    {
        $this->consentIds[$type] = $consentId;

        return $this;
    }

    /** @return list<string>|null */
    public function imageSize(): ?array
    {
        return $this->imageSize;
    }

    public function moreLabel(): string
    {
        return $this->moreLabel;
    }

    public function consentId(string $type): ?ConsentId
    {
        return $this->consentIds[$type] ?? null;
    }

    public function render(Profile $profile): string
    {
        $template = new FrontendTemplate($this->template);
        $template->setData(
            [
                'renderer' => $this,
                'fields'   => $this->fields,
                'profile'  => $profile,
            ]
        );

        return $template->parse();
    }

    public function generateDetailUrl(Profile $profile): ?string
    {
        return $this->urlGenerator->generateDetailUrl($profile);
    }

    public function hasFieldValue(string $field, Profile $profile): bool
    {
        return $this->fieldRenderer->hasValue($field, $profile);
    }

    public function parseField(string $field, Profile $profile): string
    {
        $raw = StringUtil::deserialize($profile->$field);

        return $this->fieldRenderer->render($field, $raw, $this, $profile) ?? '';
    }
}
