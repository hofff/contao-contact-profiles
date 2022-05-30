<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Environment;
use Contao\Input;
use Contao\Model;
use Contao\Pagination;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Model\Profile\Specification\InitialLastnameLetterSpecification;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRendererFactory;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function count;
use function min;
use function substr;

final class ContactProfileListController extends AbstractHybridController
{
    private ProfileRepository $profiles;

    private ContactProfileRendererFactory $rendererFactory;

    private EventDispatcherInterface $eventDispatcher;

    /** @var Adapter<Config> */
    private Adapter $configAdapter;

    /**
     * @param Adapter<Config> $configAdapter
     * @param Adapter<Input>  $inputAdapter
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        TemplateRenderer $templateRenderer,
        RequestScopeMatcher $scopeMatcher,
        ResponseTagger $responseTagger,
        RouterInterface $router,
        TranslatorInterface $translator,
        TokenChecker $tokenChecker,
        ProfileRepository $profiles,
        ContactProfileRendererFactory $rendererFactory,
        EventDispatcherInterface $eventDispatcher,
        Adapter $configAdapter,
        Adapter $inputAdapter
    ) {
        parent::__construct(
            $templateRenderer,
            $scopeMatcher,
            $responseTagger,
            $router,
            $translator,
            $tokenChecker,
            $inputAdapter
        );

        $this->profiles        = $profiles;
        $this->rendererFactory = $rendererFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->configAdapter   = $configAdapter;
    }

    protected function isBackendRequest(Request $request): bool
    {
        return $this->scopeMatcher->isBackendRequest($request);
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $renderer      = $this->rendererFactory->create($model);
        $pageParameter = $this->pageParameter($model);
        $offset        = $this->determineOffset($model, $pageParameter);
        $profiles      = $this->loadProfiles($model, $request, $offset);

        /** @psalm-suppress RedundantCast - Value might be a string */
        $total = $model->numberOfItems > 0
            ? min((int) $model->numberOfItems, $this->countTotal($model, $profiles))
            : $this->countTotal($model, $profiles);

        $data['total']         = $total;
        $data['profiles']      = $profiles;
        $data['pagination']    = $this->generatePagination($model, $total, $pageParameter);
        $data['renderer']      = $renderer;
        $data['renderProfile'] = static function (Profile $profile) use ($renderer): string {
            return $renderer->render($profile);
        };

        return $data;
    }

    /**
     * @return Profile[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @psalm-suppress MoreSpecificReturnType
     * @psalm-suppress LessSpecificReturnStatement
     */
    private function loadProfiles(Model $model, Request $request, int $offset): array
    {
        if ($model->hofff_contact_source === 'dynamic') {
            if ($this->scopeMatcher->isFrontendRequest($request)) {
                return [];
            }

            $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
            $event   = new LoadContactProfilesEvent($model, $GLOBALS['objPage'], $sources);
            $this->eventDispatcher->dispatch($event, $event::NAME);

            return $event->profiles();
        }

        /** @psalm-suppress PossiblyNullReference - Input adapter is always present */
        $specification = new InitialLastnameLetterSpecification((string) $this->inputAdapter->get('auto_item'));

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $options = [
            'order'  => $model->hofff_contact_profiles_order_sql ?: null,
            'offset' => $offset,
            'limit'  => (int) $model->numberOfItems,
        ];

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $perPage = (int) $model->perPage;
        if ($perPage > 0) {
            $options['limit'] = $perPage;
        }

        switch ($model->hofff_contact_source) {
            case 'categories':
                $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);
                $profiles    = $this->profiles->fetchPublishedByCategoriesAndSpecification(
                    $categoryIds,
                    $specification,
                    $options
                );

                return $profiles ? $profiles->getModels() : [];

            case 'custom':
            default:
                $order            = StringUtil::deserialize($model->hofff_contact_profiles_order, true);
                $options['order'] = QueryUtil::orderByIds('id', $order);
                $profileIds       = StringUtil::deserialize($model->hofff_contact_profiles, true);
                $profiles         = $this->profiles->fetchPublishedByProfileIdsAndSpecification(
                    $profileIds,
                    $specification,
                    $options
                );

                return $profiles ? $profiles->getModels() : [];
        }
    }

    private function determineOffset(Model $model, string $pageParameter): int
    {
        if ($model->perPage < 1 || $model->hofff_contact_source === 'dynamic') {
            return 0;
        }

        /** @psalm-suppress PossiblyNullReference - Input adapter is always present */
        $page = $this->inputAdapter->get($pageParameter);
        if ($page === null) {
            $page = 1;
        }

        if ($page < 1) {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        return ($page - 1) * $model->perPage * $model->perPage;
    }

    /** @param array<Profile> $profiles */
    private function countTotal(Model $model, array $profiles): int
    {
        switch ($model->hofff_contact_source) {
            case 'dynamic':
                return count($profiles);

            case 'categories':
                $categoryIds = StringUtil::deserialize($model->hofff_contact_categories, true);

                return $this->profiles->countPublishedByCategories($categoryIds);

            case 'custom':
            default:
                $profileIds = StringUtil::deserialize($model->hofff_contact_profiles, true);

                return $this->profiles->countPublishedByProfileIds($profileIds);
        }
    }

    private function generatePagination(Model $model, int $total, string $pageParameter): string
    {
        if ($model->hofff_contact_source === 'dynamic') {
            return '';
        }

        $pagination = new Pagination(
            $total,
            $model->perPage,
            $this->configAdapter->get('maxPaginationLinks'),
            $pageParameter
        );

        return $pagination->generate("\n ");
    }

    protected function pageParameter(Model $model): string
    {
        return substr($model::getTable(), 3, 1) . $model->id;
    }
}
