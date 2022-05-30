<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\SocialAccount;

final class MonolingualSocialAccount extends SocialAccount
{
    public function socialAccountId(): int
    {
        return (int) $this->id;
    }
}
