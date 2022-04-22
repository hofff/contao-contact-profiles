<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\SocialTags;

use Hofff\Contao\ContactProfiles\Model\ContactProfile;
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

    /** @param array<string,mixed> $profile */
    public function generate(array $profile): void
    {
        if ($this->socialTagsFactory === null) {
            return;
        }

        $request = $this->requestStack->getMasterRequest();
        if ($request === null) {
            return;
        }

        $model = new ContactProfile();
        $model->setRow($profile);

        $request->attributes->set(Data::class, $this->socialTagsFactory->generateByModel($model));
    }
}
