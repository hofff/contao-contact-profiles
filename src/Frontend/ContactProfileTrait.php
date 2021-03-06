<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;
use const TL_MODE;

trait ContactProfileTrait
{
    protected function compile() : void
    {
        $renderer = $this->createRenderer();

        $this->Template->profiles      = $this->loadProfiles();
        $this->Template->renderer      = $renderer;
        $this->Template->renderProfile = static function (array $profile) use ($renderer) : string {
            return $renderer->render($profile);
        };
    }

    /** @return string[][] */
    private function loadProfiles() : iterable
    {
        if ($this->hofff_contact_dynamic) {
            if (TL_MODE !== 'FE') {
                return [];
            }

            $event = new LoadContactProfilesEvent($this, $GLOBALS['objPage']);
            System::getContainer()->get('event_dispatcher')->dispatch($event::NAME, $event);

            return $event->profiles();
        }

        $query      = System::getContainer()->get(PublishedContactProfilesQuery::class);
        $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

        return $query($profileIds);
    }

    private function createRenderer() : ContactProfileRenderer
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
