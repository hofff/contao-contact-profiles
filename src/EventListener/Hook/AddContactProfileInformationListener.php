<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\StringUtil;
use Contao\Template;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use function strpos;

final class AddContactProfileInformationListener
{
    /** @var ContactProfileRepository */
    private $repository;

    /** @var string[] */
    private $templatePrefixes;

    /** @param string[] $templatePrefixes */
    public function __construct(ContactProfileRepository $repository, array $templatePrefixes)
    {
        $this->repository       = $repository;
        $this->templatePrefixes = $templatePrefixes;
    }

    /** @param mixed[] $row */
    public function onParseTemplate(Template $template) : void
    {
        if (! $this->match($template->getName())) {
            return;
        }

        $profileIds = StringUtil::deserialize($template->hofff_contact_profiles, true);

        $template->contactProfiles = $this->repository->fetchPublishedByProfileIds($profileIds);
    }

    private function match(string $templateName) : bool
    {
        foreach ($this->templatePrefixes as $prefix) {
            if (strpos($templateName, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
