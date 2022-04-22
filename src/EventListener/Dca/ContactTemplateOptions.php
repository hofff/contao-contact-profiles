<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;

final class ContactTemplateOptions
{
    /** @var ContaoFramework */
    private $framework;

    public function __construct(ContaoFramework $framework)
    {
        $this->framework = $framework;
    }

    /** @return string[] */
    public function __invoke(): array
    {
        $adapter = $this->framework->getAdapter(Controller::class);

        return $adapter->getTemplateGroup('hofff_contact_profile_');
    }
}
