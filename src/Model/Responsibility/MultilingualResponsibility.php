<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Responsibility;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;
use Override;

final class MultilingualResponsibility extends Responsibility
{
    use MultilingualTrait;

    /** @psalm-suppress RedundantCastGivenDocblockType */
    #[Override]
    public function responsibilityId(): int
    {
        return (int) $this->getLanguageId();
    }
}
