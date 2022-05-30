<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Responsibility;

use Hofff\Contao\ContactProfiles\Model\MultilingualTrait;

final class MultilingualResponsibility extends Responsibility
{
    use MultilingualTrait;

    public function responsibilityId(): int
    {
        return (int) $this->getLanguageId();
    }
}
