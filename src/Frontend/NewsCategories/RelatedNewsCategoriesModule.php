<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend\NewsCategories;

use Codefog\NewsCategoriesBundle\FrontendModule\NewsCategoriesModule;
use Codefog\NewsCategoriesBundle\Model\NewsCategoryModel;
use Contao\BackendTemplate;
use Contao\Input;
use Contao\Model\Collection;
use Contao\ModuleModel;
use Contao\ModuleNews;
use Contao\StringUtil;
use Contao\System;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Symfony\Component\HttpFoundation\RequestStack;

use function defined;
use function mb_strtoupper;

/**
 * @property int|string      $news_categoriesRoot
 * @property int|string|bool $news_resetCategories
 * @property int|string|bool $news_includeSubcategories
 * @property int|string|bool $news_enableCanonicalUrls
 * @property int|string|bool $news_showQuantity
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class RelatedNewsCategoriesModule extends NewsCategoriesModule
{
    private RepositoryManager $repositoryManager;

    private RequestStack $requestStack;

    public function __construct(ModuleModel $objModule, string $strColumn = 'main')
    {
        parent::__construct($objModule, $strColumn);

        /** @psalm-suppress PropertyTypeCoercion */
        $this->repositoryManager = self::getContainer()->get('netzmacht.contao_toolkit.repository_manager');
        /** @psalm-suppress PropertyTypeCoercion */
        $this->requestStack = self::getContainer()->get('request_stack');

        $this->news_resetCategories      = '';
        $this->news_includeSubcategories = '';
        $this->news_enableCanonicalUrls  = '';
        $this->news_showQuantity         = '';
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function generate(): string
    {
        if (defined('TL_MODE') && TL_MODE === 'BE') {
            $template = new BackendTemplate('be_wildcard');

            $template->wildcard = '### ' . mb_strtoupper($GLOBALS['TL_LANG']['FMD'][$this->type][0]) . ' ###';
            $template->title    = $this->headline;
            $template->id       = $this->id;
            $template->link     = $this->name;
            $template->href     = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        }

        /** @psalm-suppress PropertyTypeCoercion */
        $this->manager               = System::getContainer()->get('codefog_news_categories.manager');
        $this->currentNewsCategories = $this->getCurrentNewsCategories();

        return ModuleNews::generate();
    }

    protected function getCategories(): ?Collection
    {
        $profile = $this->loadProfile();
        if ($profile === null) {
            return null;
        }

        $result = $this->repositoryManager->getConnection()->executeQuery(
            'SELECT news_category_id FROM tl_contact_profile_news_category WHERE contact_profile_id=:id',
            ['id' => $profile->profileId()]
        );

        $rootId     = StringUtil::deserialize($this->news_categoriesRoot) ?: null;
        $repository = $this->repositoryManager->getRepository(NewsCategoryModel::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->findPublishedByIds($result->fetchFirstColumn(), $rootId);
    }

    private function loadProfile(): ?Profile
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return null;
        }

        $profile = $request->attributes->get(Profile::class);
        if ($profile instanceof Profile) {
            return $profile;
        }

        $repository = $this->repositoryManager->getRepository(Profile::class);

        /** @psalm-suppress UndefinedInterfaceMethod */
        return $repository->fetchPublishedByIdOrAlias((string) Input::get('auto_item'));
    }
}
