<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Override;

final class MonolingualProfile extends Profile
{
    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function profileId(): int
    {
        return (int) $this->id;
    }
}
