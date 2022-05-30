<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

final class CompositeFieldRenderer extends AbstractFieldRenderer
{
    /** @var FieldRenderer[] */
    private array $renderer;

    /**
     * @param FieldRenderer[] $renderer
     */
    public function __construct(ContaoFramework $framework, array $renderer)
    {
        parent::__construct($framework);

        $this->renderer = $renderer;
    }

    public function hasValue(string $field, Profile $profile): bool
    {
        if (isset($this->renderer[$field])) {
            return $this->renderer[$field]->hasValue($field, $profile);
        }

        return parent::hasValue($field, $profile);
    }

    /** {@inheritDoc} */
    public function render(string $field, $value, ContactProfileRenderer $renderer, Profile $profile): ?string
    {
        if (isset($this->renderer[$field])) {
            return $this->renderer[$field]->render($field, $value, $renderer, $profile);
        }

        return parent::render($field, $value, $renderer, $profile);
    }

    /** @param mixed $value */
    protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer
    ): void {
    }
}
