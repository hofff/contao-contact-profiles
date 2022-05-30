<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Contao\Template;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;

use function strpos;

/**
 * @Hook("parseTemplate")
 */
final class AddContactProfileInformationListener
{
    private ProfileRepository $repository;

    /** @var string[] */
    private array $templatePrefixes;

    /** @param string[] $templatePrefixes */
    public function __construct(ProfileRepository $repository, array $templatePrefixes)
    {
        $this->repository       = $repository;
        $this->templatePrefixes = $templatePrefixes;
    }

    public function __invoke(Template $template): void
    {
        if (! $this->match($template->getName())) {
            return;
        }

        $profileIds = StringUtil::deserialize($template->hofff_contact_profiles, true);
        $order      = StringUtil::deserialize($template->hofff_contact_profiles_order, true);
        $options    = ['order' => QueryUtil::orderByIds('id', $order)];
        $profiles   = $this->repository->fetchPublishedByProfileIds($profileIds, $options);

        $template->contactProfiles = $profiles;
    }

    private function match(string $templateName): bool
    {
        foreach ($this->templatePrefixes as $prefix) {
            if (strpos($templateName, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }
}
