<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Model;
use Hofff\Contao\ContactProfiles\Event\ProfileDetailPageEvent;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRendererFactory;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ContactProfileDetailController extends AbstractHybridController
{
    private ProfileRepository $profiles;

    private ContactProfileRendererFactory $rendererFactory;

    private EventDispatcherInterface $eventDispatcher;

    /**
     * @param Adapter<Input> $inputAdapter
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
        EventDispatcherInterface $eventDispatcher,
        ContactProfileRendererFactory $rendererFactory,
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
    }

    /** {@inheritDoc} */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $profile = $this->loadProfile();
        if ($profile === null) {
            throw new PageNotFoundException('Contact profile not found');
        }

        $this->eventDispatcher->dispatch(new ProfileDetailPageEvent($profile, $model), ProfileDetailPageEvent::NAME);

        $renderer = $this->rendererFactory->create($model);

        $data['profile']       = $profile;
        $data['renderer']      = $renderer;
        $data['renderProfile'] = static function (Profile $profile) use ($renderer): string {
            return $renderer->render($profile);
        };

        return $data;
    }

    private function loadProfile(): ?Profile
    {
        /** @psalm-suppress PossiblyNullReference - Input adapter is always set */
        return $this->profiles->fetchPublishedByIdOrAlias((string) $this->inputAdapter->get('auto_item'));
    }
}
