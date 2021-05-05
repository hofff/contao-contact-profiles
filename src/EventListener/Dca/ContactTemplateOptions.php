<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\Controller;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;

final class ContactTemplateOptions
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    public function __construct(ContaoFrameworkInterface $framework)
    {
        $this->framework = $framework;
    }

    /** @return string[] */
    public function __invoke(): array
    {
        /** @var Controller<Adapter> $adapter */
        $adapter = $this->framework->getAdapter(Controller::class);

        return $adapter->getTemplateGroup('hofff_contact_profile_');
    }
}
