<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

abstract class AbstractFieldRenderer implements FieldRenderer
{
    /** @deprecated use $template property */
    protected const TEMPLATE = null;

    /** @var ContaoFramework */
    protected $framework;

    /** @var string|null */
    protected $template = null;

    // phpcs:disable SlevomatCodingStandard.Classes.DisallowLateStaticBindingForConstants.DisallowedLateStaticBindingForConstant
    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;

        /** @psalm-suppress DeprecatedConstant */
        if (static::TEMPLATE === null) {
            return;
        }

        /** @psalm-suppress DeprecatedConstant */
        $this->template = static::TEMPLATE;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, array $profile): ?string
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        /** @var Adapter<Controller> $adpater */
        $adpater = $this->framework->getAdapter(Controller::class);
        $adpater->loadDataContainer('tl_contact_profile');
        $adpater->loadLanguageFile('tl_contact_profile');

        // phpcs:ignore
        $template = new FrontendTemplate((string) $renderer->fieldTemplate($field, $this->template));

        $template->renderer        = $renderer;
        $template->defaultTemplate = $renderer->defaultFieldTemplate();
        $template->field           = $field;
        $template->label           = $GLOBALS['TL_DCA']['tl_contact_profile']['fields'][$field]['label'][0] ?? $field;
        $template->value           = $value;
        $template->profile         = $profile;

        $this->compile($template, $value, $renderer);

        return $template->parse();
    }

    /** @param mixed $value */
    protected function hasValue($value): bool
    {
        return (bool) $value;
    }

    /** @param mixed $value */
    abstract protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void;
}
