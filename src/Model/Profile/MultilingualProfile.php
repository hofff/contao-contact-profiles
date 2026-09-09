<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;
use Override;

final class MultilingualProfile extends Profile
{
    use MultilingualTrait {
        getMultilingualQueryBuilder as public;
    }

    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function profileId(): int
    {
        return (int) $this->getLanguageId();
    }
}
