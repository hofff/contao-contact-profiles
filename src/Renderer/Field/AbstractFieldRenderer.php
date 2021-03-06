<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

abstract class AbstractFieldRenderer implements FieldRenderer
{
    protected const TEMPLATE = null;

    /** @var ContaoFrameworkInterface */
    protected $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /** {@inheritdoc} */
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, array $profile) : ?string
    {
        if (! $this->hasValue($value)) {
            return null;
        }

        /** @var Controller|Adapter $adpater */
        $adpater = $this->framework->getAdapter(Controller::class);
        $adpater->loadDataContainer('tl_contact_profile');
        $adpater->loadLanguageFile('tl_contact_profile');

        $template = new FrontendTemplate($renderer->fieldTemplate($field, static::TEMPLATE));

        $template->defaultTemplate = $renderer->defaultFieldTemplate();
        $template->field           = $field;
        $template->label           = $GLOBALS['TL_DCA']['tl_contact_profile']['fields'][$field]['label'][0] ?? $field;
        $template->value           = $value;
        $template->profile         = $profile;

        $this->compile($template, $value, $renderer);

        return $template->parse();
    }

    /** @param mixed $value */
    protected function hasValue($value) : bool
    {
        return (bool) $value;
    }

    /** @param mixed $value */
    abstract protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer) : void;
}
