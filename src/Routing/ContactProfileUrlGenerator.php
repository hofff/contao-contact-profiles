<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Routing;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use PDO;

use function array_key_exists;
use function sprintf;

final class ContactProfileUrlGenerator
{
    public const ABSOLUTE_PATH = 1;

    public const ABSOLUTE_URL = 0;

    public const PREVIEW_URL = 2;

    /** @var ContaoFramework */
    private $framework;

    /** @var Connection */
    private $connection;

    /** @var array<int|string, ?PageModel> */
    private $categoryDetailPages = [];

    public function __construct(ContaoFramework $framework, Connection $connection)
    {
        $this->framework  = $framework;
        $this->connection = $connection;
    }

    /** @param array<string,mixed> $profile */
    public function getDetailPage(array $profile): ?PageModel
    {
        if ($profile['jumpTo']) {
            return $this->framework->getAdapter(PageModel::class)->findByPk($profile['jumpTo']);
        }

        if (! array_key_exists($profile['pid'], $this->categoryDetailPages)) {
            $this->categoryDetailPages[$profile['pid']] = $this->fetchCategoryDetailPage((int) $profile['pid']);
        }

        return $this->categoryDetailPages[$profile['pid']];
    }

    /** @param array<string,mixed> $profile */
    public function generateUrlWithPage(
        array $profile,
        PageModel $pageModel,
        int $referenceType = self::ABSOLUTE_PATH
    ): string {
        $slug = '/' . ($profile['alias'] ?: $profile['id']);

        switch ($referenceType) {
            case self::ABSOLUTE_PATH:
                return $pageModel->getFrontendUrl($slug);

            case self::ABSOLUTE_URL:
                return $pageModel->getAbsoluteUrl($slug);

            case self::PREVIEW_URL:
                return $pageModel->getPreviewUrl($slug);

            default:
                throw new InvalidArgumentException(
                    sprintf('Reference type "%s" is not supported', $referenceType)
                );
        }
    }

    /** @param array<string,mixed> $profile */
    public function generateDetailUrl(array $profile, int $referenceType = self::ABSOLUTE_PATH): ?string
    {
        $page = $this->getDetailPage($profile);
        if ($page === null) {
            return null;
        }

        return $this->generateUrlWithPage($profile, $page, $referenceType);
    }

    private function fetchCategoryDetailPage(int $categoryId): ?PageModel
    {
        $statement = $this->connection->executeQuery(
            'SELECT jumpTo from tl_contact_category WHERE id = :categoryId LIMIT 0,1',
            ['categoryId' => $categoryId]
        );

        $pageId = $statement->fetch(PDO::FETCH_COLUMN);
        if ($pageId === false || $pageId < 1) {
            return null;
        }

        /** @var Adapter<PageModel> $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        return $adapter->findByPk($pageId);
    }
}
