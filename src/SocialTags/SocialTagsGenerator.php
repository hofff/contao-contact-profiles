<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\SocialTags\Data\Data;
use Hofff\Contao\SocialTags\Data\SocialTagsFactory;
use Symfony\Component\HttpFoundation\RequestStack;

final class SocialTagsGenerator
{
    public function __construct(private RequestStack $requestStack, private SocialTagsFactory|null $socialTagsFactory)
    {
    }

    public function generate(Profile $profile): void
    {
        if ($this->socialTagsFactory === null) {
            return;
        }

        $request = $this->requestStack->getMainRequest();
        if ($request === null) {
            return;
        }

        $request->attributes->set(Data::class, $this->socialTagsFactory->generate($profile));
    }
}
