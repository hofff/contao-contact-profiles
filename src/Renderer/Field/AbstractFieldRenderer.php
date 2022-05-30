<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

use function is_object;
use function is_scalar;
use function method_exists;

abstract class AbstractFieldRenderer implements FieldRenderer
{
    protected ContaoFramework $framework;

    protected ?string $template = null;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, Profile $profile): ?string
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
        $template->renderValue     = /** @psalm-return mixed|scalar */ static function () use ($value) {
            if (is_scalar($value)) {
                return $value;
            }

            if (is_object($value) && method_exists($value, '__toString')) {
                return $value->__toString();
            }

            return '';
        };

        $this->compile($template, $value, $profile, $renderer);

        return $template->parse();
    }

    /** @param mixed $value */
    protected function hasValue($value): bool
    {
        return (bool) $value;
    }

    /** @param mixed $value */
    abstract protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer
    ): void;
}
