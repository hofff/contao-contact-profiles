<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\Environment;
use Contao\Input;
use Contao\Pagination;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Util\ContactProfileUtil;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function count;
use function defined;
use function min;
use function range;
use function strlen;

use const TL_MODE;

trait ContactProfileTrait
{
    use CreateRendererTrait;

    protected function compile(): void
    {
        $renderer = $this->createRenderer();

        $pageParameter = $this->pageParameter();
        $offset        = $this->determineOffset($pageParameter);
        $profiles      = $this->loadProfiles($offset);

        /** @psalm-suppress RedundantCast - Value might be a string */
        $total = $this->numberOfItems > 0
            ? min((int) $this->numberOfItems, $this->countTotal($profiles))
            : $this->countTotal($profiles);

        $this->Template->total         = $total;
        $this->Template->profiles      = $profiles;
        $this->Template->pagination    = $this->generatePagination($total, $pageParameter);
        $this->Template->renderer      = $renderer;
        $this->Template->renderProfile = static function (array $profile) use ($renderer): string {
            return $renderer->render($profile);
        };
    }

    /**
     * @return string[][]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function loadProfiles(int $offset): iterable
    {
        if ($this->hofff_contact_source === 'dynamic') {
            if (defined('TL_MODE') && TL_MODE !== 'FE') {
                return [];
            }

            /** @psalm-var EventDispatcherInterface $dispatcher */
            $dispatcher = System::getContainer()->get('event_dispatcher');
            $sources    = StringUtil::deserialize($this->hofff_contact_sources, true);
            $event      = new LoadContactProfilesEvent($this, $GLOBALS['objPage'], $sources);
            $dispatcher->dispatch($event, $event::NAME);

            return $event->profiles();
        }

        $criteria = $this->buildCriteria();

        /** @psalm-var ContactProfileRepository $repository */
        $repository = System::getContainer()->get(ContactProfileRepository::class);
        $order      = $this->hofff_contact_profiles_order_sql ?: null;
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $limit = (int) $this->numberOfItems;
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $perPage = (int) $this->perPage;
        if ($perPage > 0) {
            $limit = $perPage;
        }

        switch ($this->hofff_contact_source) {
            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                return $repository->fetchPublishedByCategories($categoryIds, $limit, $offset, $order, $criteria);

            case 'custom':
            default:
                /** @psalm-var ContactProfileRepository $repository */
                $repository = System::getContainer()->get(ContactProfileRepository::class);
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);
                $profiles   = $repository->fetchPublishedByProfileIds($profileIds, $limit, $offset, $order, $criteria);
                $order      = StringUtil::deserialize($this->hofff_contact_profiles_order, true);
                $profiles   = ContactProfileUtil::orderListByIds($profiles, $order);

                return $profiles;
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

    /** @param list<array<string,mixed>> $profiles */
    private function countTotal(array $profiles): int
    {
        /** @psalm-var ContactProfileRepository $repository */
        $repository = System::getContainer()->get(ContactProfileRepository::class);

        switch ($this->hofff_contact_source) {
            case 'dynamic':
                return count($profiles);

            case 'categories':
                $categoryIds = StringUtil::deserialize($this->hofff_contact_categories, true);

                return $repository->countPublishedByCategories($categoryIds);

            case 'custom':
            default:
                $profileIds = StringUtil::deserialize($this->hofff_contact_profiles, true);

                return $repository->countPublishedByProfileIds($profileIds);
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

    /** @return array<string,array<string,mixed>> */
    private function buildCriteria(): array
    {
        $criteria = [];
        $letter   = (string) Input::get('auto_item');

        if ($letter === 'numeric') {
            $letters = range('a', 'z');
            foreach ($letters as $letter) {
                $criteria['p.lastname NOT LIKE :letter_' . $letter] = ['letter_' . $letter => $letter . '%'];
            }
        } elseif (strlen($letter) > 0) {
            $criteria['p.lastname LIKE :letter'] = ['letter' => $letter . '%'];
        }

        return $criteria;
    }

    abstract protected function pageParameter(): string;
}
