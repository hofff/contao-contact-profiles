<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Hofff\Contao\ContactProfiles\Model\Profile\Profile;

interface FieldRenderer
{
    public function hasValue(string $field, Profile $profile): bool;

    /**
     * @param mixed $value
     */
    public function render(string $field, $value, ContactProfileRenderer $renderer, Profile $profile): ?string;
}
