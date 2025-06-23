<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend\Profile;

use Contao\Config;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
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
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function in_array;
use function is_numeric;
use function min;
use function substr;

#[AsContentElement(
    'hofff_contact_profile_list',
    'hofff_contact_profiles',
    'ce_hofff_contact_profile_list',
    'renderAsContentElement'
)]
#[AsFrontendModule(
    'hofff_contact_profile_list',
    'hofff_contact_profiles',
    'mod_hofff_contact_profile_list',
    'renderAsFrontendModule'
)]
final class ContactProfileListController extends AbstractHybridController
{
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
        private ProfileProvider $provider,
        private ContactProfileRendererFactory $rendererFactory,
        private Adapter $configAdapter,
        Adapter $inputAdapter,
    ) {
        parent::__construct(
            $templateRenderer,
            $scopeMatcher,
            $responseTagger,
            $router,
            $translator,
            $tokenChecker,
            $inputAdapter,
        );
    }

    /** {@inheritDoc} */
    #[Override]
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
     * @return list<Profile>
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function loadProfiles(Model $model, int $offset): array
    {
        $filters       = StringUtil::deserialize($model->hofff_contact_filters, true);
        $specification = null;

        if (in_array('initials', $filters, true)) {
            /**
             * @psalm-suppress PossiblyNullReference - Input adapter is always present
             * @psalm-suppress PossiblyInvalidCast
             */
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

        if (! is_numeric($page) || $page < 1) {
            throw new PageNotFoundException('Page not found: ' . Environment::get('uri'));
        }

        return ((int) $page - 1) * $model->perPage;
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
            $pageParameter,
        );

        return $pagination->generate("\n ");
    }

    protected function pageParameter(Model $model): string
    {
        return substr($model::getTable(), 3, 1) . $model->id;
    }
}
