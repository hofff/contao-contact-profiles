<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\ServiceAnnotation\Callback;

/**
 * @Callback(table="tl_content", target="fields.hofff_contact_template.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_template.options")
 */
final class ContactTemplateOptions
{
    public function __construct(private ContaoFramework $framework)
    {
    }

    /** @return string[] */
    public function __invoke(): array
    {
        $adapter = $this->framework->getAdapter(Controller::class);

        return $adapter->getTemplateGroup('hofff_contact_profile_');
    }
}
