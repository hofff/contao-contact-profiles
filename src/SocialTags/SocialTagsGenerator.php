<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\SocialTags\Data\Data;
use Hofff\Contao\SocialTags\Data\SocialTagsFactory;
use Symfony\Component\HttpFoundation\RequestStack;

final class SocialTagsGenerator
{
    /** @var RequestStack */
    private $requestStack;

    /** @var SocialTagsFactory|null */
    private $socialTagsFactory;

    public function __construct(RequestStack $requestStack, ?SocialTagsFactory $socialTagsFactory)
    {
        $this->requestStack      = $requestStack;
        $this->socialTagsFactory = $socialTagsFactory;
    }

    public function generate(Profile $profile): void
    {
        if ($this->socialTagsFactory === null) {
            return;
        }

        $request = $this->requestStack->getMasterRequest();
        if ($request === null) {
            return;
        }

        $request->attributes->set(Data::class, $this->socialTagsFactory->generateByModel($profile));
    }
}
