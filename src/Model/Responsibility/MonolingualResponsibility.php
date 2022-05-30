<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Responsibility;

final class MonolingualResponsibility extends Responsibility
{
    public function responsibilityId(): int
    {
        return (int) $this->id;
    }
}
