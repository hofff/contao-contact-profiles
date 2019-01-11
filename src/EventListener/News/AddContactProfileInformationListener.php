<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\News;

use Contao\StringUtil;
use Contao\Template;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;

final class AddContactProfileInformationListener
{
    /** @var PublishedContactProfilesQuery */
    private $query;

    public function __construct(PublishedContactProfilesQuery $query)
    {
        $this->query = $query;
    }

    /** @param mixed[] $row */
    public function onParseArticles(Template $template, array $row) : void
    {
        $profileIds = StringUtil::deserialize($row['hofff_contact_profiles'], true);

        $template->contactProfiles = ($this->query)($profileIds);
    }
}
