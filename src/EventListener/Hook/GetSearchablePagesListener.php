<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Date;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

use function is_int;
use function is_string;

final class GetSearchablePagesListener
{
    /** @var ContaoFramework */
    private $framework;

    /** @var Connection */
    private $connection;

    /** @var ContactProfileRepository */
    private $contactProfiles;

    /** @var ContactProfileUrlGenerator */
    private $urlGenerator;

    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        ContactProfileRepository $contactProfiles,
        ContactProfileUrlGenerator $urlGenerator
    ) {
        $this->framework       = $framework;
        $this->connection      = $connection;
        $this->contactProfiles = $contactProfiles;
        $this->urlGenerator    = $urlGenerator;
    }

    /**
     * @param string[]        $pages
     * @param int|string|null $rootId
     *
     * @return string[]
     */
    public function __invoke(array $pages, $rootId = null, bool $isSitemap = false): array
    {
        $rootId      = $rootId ? (int) $rootId : null;
        $categoryIds = $this->fetchCategoriesWithDetailPage($rootId);

        foreach ($this->contactProfiles->fetchPublishedByCategories($categoryIds) as $contactProfile) {
            // Detail page of the category is overridden by the contact profile. Page is already processed by Contao.
            if ($contactProfile['jumpTo'] > 0) {
                continue;
            }

            $detailPage = $this->urlGenerator->getDetailPage($contactProfile);
            if ($detailPage === null || $this->isPageExcluded($detailPage, $isSitemap)) {
                continue;
            }

            $pages[] = $this->urlGenerator->generateUrlWithPage(
                $contactProfile,
                $detailPage,
                ContactProfileUrlGenerator::ABSOLUTE_URL
            );
        }

        return $pages;
    }

    /** @return array<array-key,mixed> */
    private function fetchCategoriesWithDetailPage(?int $rootId): array
    {
        $pageIds      = $this->getPageIds($rootId);
        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('id')
            ->from('tl_contact_category', 'c')
            ->where('jumpTo > 0');

        if ($pageIds !== []) {
            $queryBuilder
                ->andWhere('jumpTo IN (:pageIds)')
                ->setParameter('pageIds', $pageIds, Connection::PARAM_STR_ARRAY);
        }

        $result = $queryBuilder->execute();
        if (is_string($result) || is_int($result)) {
            return [];
        }

        return $result->fetchFirstColumn();
    }

    /** @return array<array-key,mixed> */
    private function getPageIds(?int $rootId): array
    {
        if ($rootId === null || $rootId === 0) {
            return [];
        }

        return $this->framework->createInstance(Database::class)->getChildRecords($rootId, 'tl_page');
    }

    private function isPageExcluded(PageModel $detailPage, bool $isSitemap): bool
    {
        $time = Date::floorToMinute();

        // The target page has not been published (see #5520)
        if (
            ! $detailPage->published
            || ((string) $detailPage->start !== '' && $detailPage->start > $time)
            || ((string) $detailPage->stop !== '' && $detailPage->stop <= $time + 60)
        ) {
            return true;
        }

        if (! $isSitemap) {
            return false;
        }

        // The target page is protected (see #8416)
        if ($detailPage->protected) {
            return true;
        }

        // The target page is exempt from the sitemap (see #6418)
        return $detailPage->sitemap === 'map_never';
    }
}
