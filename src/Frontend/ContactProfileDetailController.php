<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Contao\Controller;
use Contao\CoreBundle\Exception\PageNotFoundException;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRendererFactory;
use Hofff\Contao\ContactProfiles\SocialTags\SocialTagsGenerator;
use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController;
use Netzmacht\Contao\Toolkit\Response\ResponseTagger;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use Netzmacht\Contao\Toolkit\View\Template\TemplateRenderer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function str_replace;
use function strip_tags;
use function trim;

final class ContactProfileDetailController extends AbstractHybridController
{
    private ProfileRepository $profiles;

    private SocialTagsGenerator $socialTagsGenerator;

    /** @var Adapter<Controller> */
    private Adapter $controllerAdapter;

    private ContactProfileRendererFactory $rendererFactory;

    /**
     * @param Adapter<Controller> $controllerAdapter
     * @param Adapter<Input>      $inputAdapter
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
        SocialTagsGenerator $socialTagsGenerator,
        ContactProfileRendererFactory $rendererFactory,
        Adapter $controllerAdapter,
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

        $this->profiles            = $profiles;
        $this->socialTagsGenerator = $socialTagsGenerator;
        $this->rendererFactory     = $rendererFactory;
        $this->controllerAdapter   = $controllerAdapter;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    protected function isBackendRequest(Request $request): bool
    {
        return $this->scopeMatcher->isBackendRequest($request);
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function prepareTemplateData(array $data, Request $request, Model $model): array
    {
        $profile = $this->loadProfile();
        if ($profile === null) {
            throw new PageNotFoundException('Contact profile not found');
        }

        $GLOBALS['objPage']->pageTitle   = trim($profile->firstname . ' ' . $profile->lastname);
        $GLOBALS['objPage']->description = $this->prepareMetaDescription((string) $profile->teaser);

        $this->socialTagsGenerator->generate($profile);

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

    private function prepareMetaDescription(string $text): string
    {
        $text = $this->controllerAdapter->replaceInsertTags($text, false);
        $text = strip_tags($text);
        $text = str_replace("\n", ' ', $text);
        $text = StringUtil::substr($text, 320);

        return trim($text);
    }
}
