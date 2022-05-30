<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="tl_content", target="fields.hofff_contact_sources.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_sources.options")
 */
final class DynamicSourcesOptions
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
