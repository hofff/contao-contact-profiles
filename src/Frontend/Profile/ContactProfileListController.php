<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend\Profile;

use Contao\Config;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Environment;
use Contao\Input;
use Contao\Model;
use Contao\Pagination;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Frontend\AbstractHybridController;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\Specification\InitialLastnameLetterSpecification;
use Hofff\Contao\ContactProfiles\Provider\ProfileProvider;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRendererFactory;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;
use function min;
use function substr;

final class ContactProfileListController extends AbstractHybridController
{
    private ContactProfileRendererFactory $rendererFactory;

    private ProfileProvider $provider;

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
        ProfileProvider $provider,
        ContactProfileRendererFactory $rendererFactory,
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

        $this->provider        = $provider;
        $this->rendererFactory = $rendererFactory;
        $this->configAdapter   = $configAdapter;
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $renderer      = $this->rendererFactory->create($model);
        $pageParameter = $this->pageParameter($model);
        $offset        = $this->determineOffset($model, $pageParameter);
        $profiles      = $this->loadProfiles($model, $offset);

        /** @psalm-suppress RedundantCast - Value might be a string */
        $total = $model->numberOfItems > 0
            ? min((int) $model->numberOfItems, $this->provider->countTotal($model, $profiles))
            : $this->provider->countTotal($model, $profiles);

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
    private function loadProfiles(Model $model, int $offset): array
    {
        $filters       = StringUtil::deserialize($model->hofff_contact_filters, true);
        $specification = null;

        if (in_array('initials', $filters, true)) {
            /** @psalm-suppress PossiblyNullReference - Input adapter is always present */
            $specification = new InitialLastnameLetterSpecification((string) $this->inputAdapter->get('auto_item'));
        }

        return $this->provider->fetchProfiles($model, $GLOBALS['objPage'], $specification, $offset);
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
