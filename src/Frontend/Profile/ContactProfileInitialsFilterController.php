<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend\Profile;

use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\DependencyInjection\Attribute\AsFrontendModule;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Model;
use Hofff\Contao\ContactProfiles\Provider\ProfileProvider;
use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsContentElement(
    'hofff_contact_profile_initials_filter',
    'hofff_contact_profiles',
    'ce_hofff_contact_profile_initials_filter',
    'renderAsContentElement'
)]
#[AsFrontendModule(
    'hofff_contact_profile_initials_filter',
    'hofff_contact_profiles',
    'mod_hofff_contact_profile_initials_filter',
    'renderAsFrontendModule'
)]
final class ContactProfileInitialsFilterController extends AbstractHybridController
{
    /** @param Adapter<Input> $inputAdapter */
    public function __construct(
        TemplateRenderer $templateRenderer,
        RequestScopeMatcher $scopeMatcher,
        ResponseTagger $responseTagger,
        RouterInterface $router,
        TranslatorInterface $translator,
        TokenChecker $tokenChecker,
        private ProfileProvider $provider,
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

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    #[Override]
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        /**
         * @psalm-suppress PossiblyNullReference - Input adapter is always set
         * @psalm-suppress PossiblyInvalidCast
         */
        $data['activeLetter'] = (string) $this->inputAdapter->get('auto_item');
        $data['letters']      = $this->provider->calculateInitials($model, $GLOBALS['objPage']);
        $data['resetUrl']     = $GLOBALS['objPage']->getFrontendUrl();
        $data['filterUrl']    = static function (string $letter): string {
            return $GLOBALS['objPage']->getFrontendUrl('/' . $letter);
        };

        return $data;
    }
}
