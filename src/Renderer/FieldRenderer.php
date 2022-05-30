<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

use Hofff\Contao\ContactProfiles\Model\Profile\Profile;

interface FieldRenderer
{
    /**
     * @param mixed $value
     */
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, Profile $profile): ?string;
}
