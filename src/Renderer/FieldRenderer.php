<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Renderer;

interface FieldRenderer
{
    public function __invoke(string $field, $value, ContactProfileRenderer $renderer, array $profile): ?string;
}
