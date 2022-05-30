<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Routing;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use InvalidArgumentException;
use PDO;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\RouterInterface;

use function array_key_exists;
use function sprintf;

final class ContactProfileUrlGenerator
{
    public const ABSOLUTE_PATH = 1;

    public const ABSOLUTE_URL = 0;

    public const PREVIEW_URL = 2;

    private ContaoFramework $framework;

    private Connection $connection;

    private RouterInterface $router;

    /** @var array<int|string, ?PageModel> */
    private array $categoryDetailPages = [];

    private ?string $previewScript;

    public function __construct(
        ContaoFramework $framework,
        Connection $connection,
        RouterInterface $router,
        ?string $previewScript
    ) {
        $this->framework     = $framework;
        $this->connection    = $connection;
        $this->router        = $router;
        $this->previewScript = $previewScript;
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    public function getDetailPage(Profile $profile): ?PageModel
    {
        if ($profile->jumpTo) {
            return $this->framework->getAdapter(PageModel::class)->findByPk($profile->jumpTo);
        }

        if (! array_key_exists($profile->pid, $this->categoryDetailPages)) {
            $this->categoryDetailPages[$profile->pid] = $this->fetchCategoryDetailPage((int) $profile->pid);
        }

        return $this->categoryDetailPages[$profile->pid];
    }

    public function generateUrlWithPage(
        Profile $profile,
        PageModel $pageModel,
        int $referenceType = self::ABSOLUTE_PATH
    ): string {
        $slug = '/' . ($profile->alias ?: $profile->profileId());

        if ($pageModel->type === 'contact_profile') {
            return $this->generateUrlForContactPage($profile, $pageModel, $referenceType);
        }

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

    public function generateDetailUrl(Profile $profile, int $referenceType = self::ABSOLUTE_PATH): ?string
    {
        $page = $this->getDetailPage($profile);
        if ($page === null) {
            return null;
        }

        return $this->generateUrlWithPage($profile, $page, $referenceType);
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
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

    private function generateUrlForContactPage(Profile $profile, PageModel $pageModel, int $referenceType): string
    {
        switch ($referenceType) {
            case self::ABSOLUTE_PATH:
                return $this->router->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::CONTENT_OBJECT => $pageModel,
                        'alias'                              => $profile->alias ?: $profile->id,
                    ],
                    RouterInterface::ABSOLUTE_PATH,
                );

            case self::ABSOLUTE_URL:
                return $this->router->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::CONTENT_OBJECT => $pageModel,
                        'alias'                              => $profile->alias ?: $profile->id,
                    ],
                    RouterInterface::ABSOLUTE_URL,
                );

            case self::PREVIEW_URL:
                $baseUrl = '';
                if ($this->previewScript) {
                    $baseUrl = $this->router->getContext()->getBaseUrl();
                    $this->router->getContext()->setBaseUrl($this->previewScript);
                }

                $url = $this->router->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::CONTENT_OBJECT => $pageModel,
                        'alias'                              => $profile->alias ?: $profile->id,
                    ],
                    RouterInterface::ABSOLUTE_PATH,
                );

                if ($this->previewScript) {
                    $this->router->getContext()->setBaseUrl($baseUrl);
                }

                return $url;

            default:
                throw new InvalidArgumentException(
                    sprintf('Reference type "%s" is not supported', $referenceType)
                );
        }
    }
}
