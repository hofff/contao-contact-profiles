<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Input;
use Contao\PageModel;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRenderer;

use function strlen;
use const TL_MODE;

trait ContactProfileTrait
{
    use CreateRendererTrait{
        createRenderer as createRendererParent;
    }

    protected function compile() : void
    {
        $renderer = $this->createRenderer();

        $pageParameter = $this->pageParameter();
        $offset        = $this->determineOffset($pageParameter);
        $profiles      = $this->loadProfiles($offset);
        $total         = $this->numberOfItems > 0
            ? min((int) $this->numberOfItems, $this->countTotal($profiles))
            : $this->countTotal($profiles);

        $this->Template->total         = $total;
        $this->Template->profiles      = $profiles;
        $this->Template->pagination    = $this->generatePagination($total, $pageParameter);
        $this->Template->renderer      = $renderer;
        $this->Template->renderProfile = static function (array $profile) use ($renderer) : string {
            return $renderer->render($profile);
        };
    }

    /**
     * @return string[][]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function loadProfiles(int $offset) : iterable
    {
        if ($this->hofff_contact_source === 'dynamic') {
            if (TL_MODE !== 'FE') {
                return [];
            }

            $sources = StringUtil::deserialize($this->hofff_contact_sources, true);
            $event = new LoadContactProfilesEvent($this, $GLOBALS['objPage'], $sources);
            System::getContainer()->get('event_dispatcher')->dispatch($event::NAME, $event);

            return $event->profiles();
        }

        $criteria = $this->buildCriteria();

        $repository = System::getContainer()->get(ContactProfileRepository::class);
        $order      = $this->hofff_contact_profiles_order_sql ?: null;
        $limit      = (int) $this->numberOfItems;
        $perPage    = (int) $this->perPage;
        if ($perPage > 0) {
            $limit = $perPage;
        }

        switch ($this->hofff_contact_source) {
            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                return $repository->fetchPublishedByCategories($categoryIds, $limit, $offset, $order, $criteria);

            case 'custom':
            default:
                $repository = System::getContainer()->get(ContactProfileRepository::class);
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                return $repository->fetchPublishedByProfileIds($profileIds, $limit, $offset, $order, $criteria);
        }
    }

    private function determineOffset(string $pageParameter): int
    {
        if ($this->perPage < 1 || $this->hofff_contact_source === 'dynamic') {
            return 0;
        }

        $page = Input::get($pageParameter);
        if ($page === null) {
            $page = 1;
        }

        if ($page < 1) {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        return ($page - 1) * $this->perPage * $this->perPage;
    }

    private function countTotal(array $profiles): int
    {
        switch ($this->hofff_contact_source) {
            case 'dynamic':
                return count($profiles);

            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                return System::getContainer()->get(ContactProfileRepository::class)
                    ->countPublishedByCategories($categoryIds);

            case 'custom':
            default:
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                return System::getContainer()->get(ContactProfileRepository::class)
                    ->countPublishedByProfileIds($profileIds);
        }
    }

    private function generatePagination(int $total, string $pageParameter): string
    {
        if ($this->hofff_contact_source === 'dynamic') {
            return '';
        }

        return (new Pagination($total, $this->perPage, Config::get('maxPaginationLinks'), $pageParameter))
            ->generate("\n ");
    }

    private function buildCriteria(): array
    {
        $criteria = [];
        $letter   = (string) Input::get('letter');

        if (strlen($letter) > 0) {
            $criteria['p.lastname LIKE :letter'] = ['letter' => Input::get('letter') . '%'];
        }

        return $criteria;
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

    abstract protected function pageParameter(): string;
}
