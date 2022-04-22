<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\System;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\SocialTags\SocialTagsGenerator;
use Symfony\Component\HttpFoundation\RequestStack;

use function trim;

trait ContactProfileDetailTrait
{
    use CreateRendererTrait;

    /** @var array<string,mixed>|null */
    private $profile;

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function generate(): string
    {
        /** @psalm-var RequestStack $requestStack */
        $requestStack = System::getContainer()->get('request_stack');
        $request      = $requestStack->getCurrentRequest();

        /** @psalm-var ScopeMatcher $scopeMatcher */
        $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');

        if ($request && $scopeMatcher->isBackendRequest($request)) {
            return $this->renderBackendWildcard();
        }

        $this->profile = $this->loadProfile();
        if ($this->profile === null) {
            throw new PageNotFoundException('Contact profile not found');
        }

        $GLOBALS['objPage']->pageTitle   = trim($this->profile['firstname'] . ' ' . $this->profile['lastname']);
        $GLOBALS['objPage']->description = $this->prepareMetaDescription((string) $this->profile['teaser']);

        /** @psalm-var SocialTagsGenerator $socialTagsGenerator */
        $socialTagsGenerator = System::getContainer()->get(SocialTagsGenerator::class);
        $socialTagsGenerator->generate($this->profile);

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

    /** @return array<string,mixed> */
    private function loadProfile(): ?array
    {
        /** @psalm-var ContactProfileRepository $repository */
        $repository = System::getContainer()->get(ContactProfileRepository::class);

        return $repository->fetchPublishedByIdOrAlias((string) Input::get('auto_item'));
    }

    abstract protected function renderBackendWildcard(): string;
}
