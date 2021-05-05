<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

final class SourcesOptions
{
    /** @var list<string> */
    private $sources;

    /** @param list<string> $sources */
    public function __construct(array $sources)
    {
        $this->sources = $sources;
    }

    /** @return list<string> */
    public function __invoke(): array
    {
        return $this->sources;
    }
}
