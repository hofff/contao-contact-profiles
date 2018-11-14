<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class EmailFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_email';

    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
    }
}
