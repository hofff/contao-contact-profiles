<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\SocialAccount;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;

final class MultilingualSocialAccount extends SocialAccount
{
    use MultilingualTrait;

    public function socialAccountId(): int
    {
        return (int) $this->getLanguageId();
    }
}
