<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer\Field;

use Contao\FrontendTemplate;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

final class JumpToFieldRenderer extends AbstractFieldRenderer
{
    protected const TEMPLATE = 'hofff_contact_field_jump_to';

    /** @param mixed $value */
    protected function compile(FrontendTemplate $template, $value, ContactProfileRenderer $renderer): void
    {
        $template->label = $renderer->moreLabel();

        $value = $this->framework->getAdapter(PageModel::class)->findByPk($value);
        if (! $value instanceof PageModel) {
            $template->value = null;

            return;
        }

        $template->value = $value->getFrontendUrl();
    }
}
