<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Responsibility\ResponsibilityRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class ResponsibilitiesRenderer extends AbstractFieldRenderer
{
    /** @var string|null */
    protected $template = 'hofff_contact_field_responsibilities';

    private ResponsibilityRepository $responsibilities;

    public function __construct(ContaoFramework $framework, ResponsibilityRepository $responsibilities)
    {
        parent::__construct($framework);

        $this->responsibilities = $responsibilities;
    }

    /** @param mixed $value */
    protected function compile(
        FrontendTemplate $template,
        $value,
        Profile $profile,
        ContactProfileRenderer $renderer
    ): void {
        $template->value = $this->responsibilities->findMultipleByIds((array) $value);
    }
}
