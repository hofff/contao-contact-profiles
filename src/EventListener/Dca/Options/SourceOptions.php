<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Hofff\Contao\ContactProfiles\Provider\ProfileProvider;

/**
 * @Callback(table="tl_content", target="fields.hofff_contact_source.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_source.options")
 */
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
