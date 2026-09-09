<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Hofff\Contao\ContactProfiles\Provider\ProfileProvider;

#[AsCallback('tl_content', 'fields.hofff_contact_source.options')]
#[AsCallback('tl_module', 'fields.hofff_contact_source.options')]
final class SourceOptions
{
    /** @param iterable<ProfileProvider> $providers */
    public function __construct(private iterable $providers)
    {
    }

    /** @return list<string> */
    public function __invoke(): array
    {
        $options = [];

        foreach ($this->providers as $provider) {
            $options[] = $provider->name();
        }

        return $options;
    }
}
