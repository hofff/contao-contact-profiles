<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Responsibility;

use Override;

final class MonolingualResponsibility extends Responsibility
{
    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function responsibilityId(): int
    {
        return (int) $this->id;
    }
}
