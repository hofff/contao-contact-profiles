<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\PageModel;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesByCategoriesQuery;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;

use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;
use const TL_MODE;

trait ContactProfileTrait
{
    use CreateRendererTrait{
        createRenderer as createRendererParent;
    }

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
        switch ($this->hofff_contact_source) {
            case 'dynamic':
                if (TL_MODE !== 'FE') {
                    return [];
                }

                $sources = StringUtil::deserialize($this->hofff_contact_sources, true);
                $event = new LoadContactProfilesEvent($this, $GLOBALS['objPage'], $sources);
                System::getContainer()->get('event_dispatcher')->dispatch($event::NAME, $event);

                return $event->profiles();

            case 'categories':
                $query       = System::getContainer()->get(PublishedContactProfilesByCategoriesQuery::class);
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                return $query($categoryIds);

            case 'custom':
            default:
                $query      = System::getContainer()->get(PublishedContactProfilesQuery::class);
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                return $query($profileIds);
        }
    }

    protected function createRenderer(): ContactProfileRenderer
    {
        $renderer = $this->createRendererParent();

        if ($this->hofff_contact_jump_to) {
            $pageModel = PageModel::findByPk($this->hofff_contact_jump_to);
            if ($pageModel) {
                $renderer->withDetailPage($pageModel);
            }
        }

        return $renderer;
    }


}
