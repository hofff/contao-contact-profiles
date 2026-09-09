<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\Controller;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\CoreBundle\Framework\ContaoFramework;

#[AsCallback('tl_content', 'fields.hofff_contact_template.options')]
#[AsCallback('tl_module', 'fields.hofff_contact_template.options')]
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
