<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

final class MonolingualProfile extends Profile
{
    public function profileId(): int
    {
        return (int) $this->id;
    }
}
