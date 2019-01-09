<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Contao\FrontendTemplate;
use Contao\StringUtil;

final class ContactProfileRenderer
{
    private const DEFAULT_TEMPLATE = 'hofff_contact_profile_default';

    private const DEFAULT_FIELD_TEMPLATE = 'hofff_contact_field';

    /** @var FieldRenderer */
    private $fieldRenderer;

    private $fields = [];

    /** @var array|null */
    private $imageSize;

    private $template = self::DEFAULT_TEMPLATE;

    private $fieldTemplates = [];

    /** @var string */
    private $defaultFieldTemplate;

    /** @var string */
    private $moreLabel;

    public function __construct(FieldRenderer $fieldRenderer, string $moreLabel)
    {
        $this->fieldRenderer = $fieldRenderer;
        $this->moreLabel = $moreLabel;
        $this->defaultFieldTemplate = self::DEFAULT_FIELD_TEMPLATE;
    }

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

    public function withImageSize(array $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function imageSize(): ?array
    {
        return $this->imageSize;
    }

    public function moreLabel(): string
    {
        return $this->moreLabel;
    }

    public function render(array $profile): string
    {
        $template = new FrontendTemplate($this->template);
        $template->setData(
            [
                'fields'  => $this->parseFields($profile),
                'profile' => array_map([StringUtil::class, 'deserialize'], $profile),
                'has'     => function (string $field) use ($template): bool {
                    return !empty($template->fields[$field]);
                },
            ]
        );

        return $template->parse();
    }

    private function parseFields(array $profile): array
    {
        $rendered = [];

        foreach ($this->fields as $field) {
            $raw              = StringUtil::deserialize($profile[$field] ?? null);
            $rendered[$field] = $this->fieldRenderer->__invoke($field, $raw, $this, $profile);
        }

        return $rendered;
    }
}
