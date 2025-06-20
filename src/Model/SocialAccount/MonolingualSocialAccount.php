<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\SocialAccount;

use Override;

final class MonolingualSocialAccount extends SocialAccount
{
    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function socialAccountId(): int
    {
        return (int) $this->id;
    }
}
