<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class WebsiteFieldRenderer extends AbstractFieldRenderer
{
    /** @var string|null */
    protected $template = 'hofff_contact_field_website';

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $template->linkTitle = $template->profile['websiteTitle'] ?: $value;
    }
}
