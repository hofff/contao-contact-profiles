<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Database;
use Contao\Date;
use Contao\PageModel;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;

use function assert;

#[AsHook('getSearchablePages')]
final class GetSearchablePagesListener
{
    public function __construct(
        private ContaoFramework $framework,
        private Connection $connection,
        private ProfileRepository $contactProfiles,
        private ContactProfileUrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @param string[]                $pages
     * @param numeric-string|int|null $rootId
     *
     * @return string[]
     */
    public function __invoke(array $pages, $rootId = null, bool $isSitemap = false, string|null $language = null): array
    {
        $rootId ??= (int) $rootId;
        /** @psalm-suppress PossiblyInvalidArgument */
        $categoryIds = $this->fetchCategoriesWithDetailPage($rootId);
        $collection  = $this->contactProfiles->fetchPublishedByCategories(
            $categoryIds,
            ['language' => $language],
        ) ?: [];

        foreach ($collection as $contactProfile) {
            assert($contactProfile instanceof Profile);

            // Detail page of the category is overridden by the contact profile. Page is already processed by Contao.
            if ($contactProfile->jumpTo > 0) {
                continue;
            }

            $detailPage = $this->urlGenerator->getDetailPage($contactProfile);
            if ($detailPage === null || $this->isPageExcluded($detailPage, $isSitemap)) {
                continue;
            }

            $pages[] = $this->urlGenerator->generateUrlWithPage(
                $contactProfile,
                $detailPage,
                ContactProfileUrlGenerator::ABSOLUTE_URL,
            );
        }

        return $pages;
    }

    /**
     * @return list<int|string>
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function fetchCategoriesWithDetailPage(int|null $rootId): array
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
                ->setParameter('pageIds', $pageIds, ArrayParameterType::STRING);
        }

        return $queryBuilder->executeQuery()->fetchFirstColumn();
    }

    /** @return array<array-key,mixed> */
    private function getPageIds(int|null $rootId): array
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
