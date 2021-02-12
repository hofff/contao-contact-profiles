<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

final class SourcesOptions
{
    /** @var array */
    private $sources;

    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    public function __invoke(): array
    {
        return $this->sources;
    }
}
