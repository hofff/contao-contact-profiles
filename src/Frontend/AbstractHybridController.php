<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Frontend;

use Netzmacht\Contao\Toolkit\Controller\Hybrid\AbstractHybridController as BaseHybridControllerAlias;
use Override;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractHybridController extends BaseHybridControllerAlias
{
    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    #[Override]
    protected function isBackendRequest(Request $request): bool
    {
        return $this->scopeMatcher->isBackendRequest($request);
    }
}
