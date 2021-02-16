<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\StringUtil;
use function array_map;

final class ContactProfileRenderer
{
    private const DEFAULT_TEMPLATE = 'hofff_contact_profile_default';

    private const DEFAULT_FIELD_TEMPLATE = 'hofff_contact_field';

    /** @var FieldRenderer */
    private $fieldRenderer;

    /** @var string[] */
    private $fields = [];

    /** @var string[]|null */
    private $imageSize;

    /** @var string */
    private $template = self::DEFAULT_TEMPLATE;

    /** @var string[] */
    private $fieldTemplates = [];

    /** @var string */
    private $defaultFieldTemplate;

    /** @var string */
    private $moreLabel;

    /** @var PageModel|null */
    private $detailPage;

    public function __construct(FieldRenderer $fieldRenderer, string $moreLabel)
    {
        $this->fieldRenderer        = $fieldRenderer;
        $this->moreLabel            = $moreLabel;
        $this->defaultFieldTemplate = self::DEFAULT_FIELD_TEMPLATE;
    }

    /** @param string[] $fields */
    public function withFields(array $fields) : self
    {
        $this->fields = $fields;

        return $this;
    }

    public function withTemplate(string $template) : self
    {
        $this->template = $template;

        return $this;
    }

    public function withDefaultFieldTemplate(string $template) : self
    {
        $this->defaultFieldTemplate = $template;

        return $this;
    }

    public function defaultFieldTemplate() : string
    {
        return $this->defaultFieldTemplate;
    }

    public function withFieldTemplate(string $field, string $template) : self
    {
        $this->fieldTemplates[$field] = $template;

        return $this;
    }

    public function withDetailPage(PageModel $pageModel) :self
    {
        $this->detailPage = $pageModel;

        return $this;
    }

    public function fieldTemplate(string $field, ?string $default = null) : ?string
    {
        if (isset($this->fieldTemplates[$field])) {
            return $this->fieldTemplates[$field];
        }

        return $default ?: $this->defaultFieldTemplate;
    }

    /** @param string[] $imageSize */
    public function withImageSize(array $imageSize) : self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    /** @return string[]|null */
    public function imageSize() : ?array
    {
        return $this->imageSize;
    }

    public function moreLabel() : string
    {
        return $this->moreLabel;
    }

    public function detailPage(): ?PageModel
    {
        return $this->detailPage;
    }

    /** @param string[] $profile */
    public function render(array $profile) : string
    {
        $template = new FrontendTemplate($this->template);
        $template->setData(
            [
                'renderer' => $this,
                'fields'   => $this->fields,
                'profile'  => array_map([StringUtil::class, 'deserialize'], $profile),
                'has'      => static function (string $field) use ($template) : bool {
                    return ! empty($template->profile[$field]);
                },
            ]
        );

        return $template->parse();
    }

    /** @param string[] $profile */
    public function parseField(string $field, array $profile) : string
    {
        $raw = StringUtil::deserialize($profile[$field] ?? null);

        return ($this->fieldRenderer)($field, $raw, $this, $profile);
    }
}
