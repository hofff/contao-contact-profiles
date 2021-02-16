<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

trait CreateRendererTrait
{
    protected function createRenderer(): ContactProfileRenderer
    {
        $fieldRenderer = System::getContainer()->get(FieldRenderer::class);
        $moreLabel     = (string) $this->hofff_contact_more ?: $GLOBALS['TL_LANG']['MSC']['more'];
        $renderer      = (new ContactProfileRenderer($fieldRenderer, $moreLabel))
            ->withFields(StringUtil::deserialize($this->hofff_contact_fields, true));

        if (TL_MODE === 'FE' && $this->hofff_contact_template) {
            $renderer->withTemplate($this->hofff_contact_template);
        }

        $size = StringUtil::deserialize($this->size, true);
        if ($size) {
            $renderer->withImageSize($size);
        }

        return $renderer;
    }
}