<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\SocialAccount;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;
use Override;

final class MultilingualSocialAccount extends SocialAccount
{
    use MultilingualTrait;

    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function socialAccountId(): int
    {
        return (int) $this->getLanguageId();
    }
}
