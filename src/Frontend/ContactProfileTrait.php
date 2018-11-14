<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;

trait ContactProfileTrait
{
    protected function compile(): void
    {
        $this->Template->profiles      = $this->loadProfiles();
        $this->Template->renderProfile = $this->createRenderer();
    }

    private function loadProfiles(): iterable
    {
        $query      = System::getContainer()->get(PublishedContactProfilesQuery::class);
        $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

        return $query($profileIds);
    }

    private function createRenderer(): ContactProfileRenderer
    {
        $fieldRenderer = System::getContainer()->get(FieldRenderer::class);
        $renderer      = (new ContactProfileRenderer($fieldRenderer))
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
