<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

final class CompositeFieldRenderer extends AbstractFieldRenderer
{
    /** @var FieldRenderer[] */
    private $renderer;

    /**
     * @param FieldRenderer[] $renderer
     */
    public function __construct(ContaoFramework $framework, array $renderer)
    {
        parent::__construct($framework);

        $this->renderer = $renderer;
    }

    /** {@inheritDoc} */
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, array $profile): ?string
    {
        if (isset($this->renderer[$field])) {
            return $this->renderer[$field]($field, $value, $renderer, $profile);
        }

        return parent::__invoke($field, $value, $renderer, $profile);
    }

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
    }
}
