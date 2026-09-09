<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;

#[AsCallback('tl_content', 'fields.hofff_contact_sources.options')]
#[AsCallback('tl_module', 'fields.hofff_contact_sources.options')]
final class DynamicSourcesOptions
{
    /** @param list<string> $sources */
    public function __construct(private array $sources)
    {
    }

    /** @return list<string> */
    public function __invoke(): array
    {
        return $this->sources;
    }
}
