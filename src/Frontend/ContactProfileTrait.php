<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

trait ContactProfileTrait
{
    protected function compile(): void
    {
        $this->Template->profiles = $this->loadProfiles();
        $this->Template->renderer = $this->createRenderer();
    }

    private function loadProfiles(): iterable
    {
        $query      = System::getContainer()->get(PublishedContactProfilesQuery::class);
        $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

        return $query($profileIds);
    }

    private function createRenderer(): ContactProfileRenderer
    {
        $renderer = (new ContactProfileRenderer())
            ->withFields(StringUtil::deserialize($this->hofff_contact_fields, true));

        if (TL_MODE === 'FE') {
            $renderer->withTemplate($this->hofff_contact_template);
        }

        return $renderer;
    }
}
