<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

final class CompositeFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field';

    /** @var FieldRenderer[] */
    private $renderer;

    /**
     * CompositeFieldRenderer constructor.
     *
     * @param ContaoFrameworkInterface $framework
     * @param array| FieldRenderer[]   $renderer
     */
    public function __construct(ContaoFrameworkInterface $framework, array $renderer)
    {
        parent::__construct($framework);

        $this->renderer = $renderer;
    }

    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, array $profile): ?string
    {
        if (isset($this->renderer[$field])) {
            return $this->renderer[$field]($field, $value, $renderer, $profile);
        }

        return parent::__invoke($field, $value, $renderer, $profile);
    }

    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
    }
}
