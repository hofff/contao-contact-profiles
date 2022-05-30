<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend\Profile;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Model;
use Hofff\Contao\ContactProfiles\Provider\ProfileProvider;
use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactProfileInitialsFilterController extends AbstractHybridController
{
    private ProfileProvider $provider;

    /** @param Adapter<Input> $inputAdapter */
    public function __construct(
        TemplateRenderer $templateRenderer,
        RequestScopeMatcher $scopeMatcher,
        ResponseTagger $responseTagger,
        RouterInterface $router,
        TranslatorInterface $translator,
        TokenChecker $tokenChecker,
        ProfileProvider $provider,
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

        $this->provider = $provider;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        /** @psalm-suppress PossiblyNullReference - Input adapter is always set */
        $data['activeLetter'] = (string) $this->inputAdapter->get('auto_item');
        $data['letters']      = $this->provider->calculateInitials($model, $GLOBALS['objPage']);
        $data['resetUrl']     = $GLOBALS['objPage']->getFrontendUrl();
        $data['filterUrl']    = static function (string $letter): string {
            return $GLOBALS['objPage']->getFrontendUrl('/' . $letter);
        };

        return $data;
    }
}
