<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Routing;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Category\CategoryRepository;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use InvalidArgumentException;
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

    private RouterInterface $router;

    private CategoryRepository $categories;

    /** @var array<int|string, ?PageModel> */
    private array $categoryDetailPages = [];

    private ?string $previewScript;

    public function __construct(
        ContaoFramework $framework,
        RouterInterface $router,
        CategoryRepository $categories,
        ?string $previewScript
    ) {
        $this->framework     = $framework;
        $this->router        = $router;
        $this->previewScript = $previewScript;
        $this->categories    = $categories;
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
            $this->categoryDetailPages[$profile->pid] = $this->fetchCategoryDetailPage($profile);
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
    private function fetchCategoryDetailPage(Profile $profile): ?PageModel
    {
        $category = $this->categories->findOneBy(['.id=?'], [$profile->pid]);
        if (! $category) {
            return null;
        }

        /** @var Adapter<PageModel> $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        return $adapter->findByPk($category->jumpTo);
    }

    private function generateUrlForContactPage(Profile $profile, PageModel $pageModel, int $referenceType): string
    {
        switch ($referenceType) {
            case self::ABSOLUTE_PATH:
                return $this->router->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::CONTENT_OBJECT => $pageModel,
                        'alias'                              => $profile->alias ?: $profile->profileId(),
                    ],
                    RouterInterface::ABSOLUTE_PATH,
                );

            case self::ABSOLUTE_URL:
                return $this->router->generate(
                    RouteObjectInterface::OBJECT_BASED_ROUTE_NAME,
                    [
                        RouteObjectInterface::CONTENT_OBJECT => $pageModel,
                        'alias'                              => $profile->alias ?: $profile->profileId(),
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
                        'alias'                              => $profile->alias ?: $profile->profileId(),
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
