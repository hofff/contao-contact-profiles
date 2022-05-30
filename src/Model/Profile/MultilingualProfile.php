<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;

final class MultilingualProfile extends Profile
{
    use MultilingualTrait {
        getMultilingualQueryBuilder as public;
    }

    public function profileId(): int
    {
        return (int) $this->getLanguageId();
    }
}
