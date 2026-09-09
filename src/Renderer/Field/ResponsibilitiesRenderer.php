<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Responsibility\ResponsibilityRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Override;

final class ResponsibilitiesRenderer extends AbstractFieldRenderer
{
    protected string|null $template = 'hofff_contact_field_responsibilities';

    public function __construct(ContaoFramework $framework, private ResponsibilityRepository $responsibilities)
    {
        parent::__construct($framework);
    }

    /** @param mixed $value */
    #[Override]
    protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer,
    ): void {
        $template->value = $this->responsibilities->findMultipleByIds((array) $value);
    }
}
