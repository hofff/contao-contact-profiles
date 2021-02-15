<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Input;
use Contao\System;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfileQuery;

trait ContactProfileDetailTrait
{
    use CreateRendererTrait;

    /** @var array|null */
    private $profile;

    public function generate(): string
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->renderBackendWildcard();
        }

        $this->profile = $this->loadProfile();
        if ($this->profile === null) {
            throw new PageNotFoundException('Contact profile not found');
        }

        $GLOBALS['objPage']->pageTitle = trim($this->profile['firstname'] . ' ' . $this->profile['lastname']);

        return parent::generate();
    }

    protected function compile(): void
    {
        $renderer = $this->createRenderer();

        $this->Template->profile       = $this->profile;
        $this->Template->renderer      = $renderer;
        $this->Template->renderProfile = static function (array $profile) use ($renderer): string {
            return $renderer->render($profile);
        };
    }

    /** @return string[][] */
    private function loadProfile(): ?array
    {
        $query = System::getContainer()->get(PublishedContactProfileQuery::class);

        return $query((string) Input::get('auto_item'));
    }

    abstract protected function renderBackendWildcard(): string;
}
