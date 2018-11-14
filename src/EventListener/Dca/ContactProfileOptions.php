<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Hofff\Contao\ContactProfiles\Query\CategorizedContactProfilesQuery;

final class ContactProfileOptions
{
    /** @var CategorizedContactProfilesQuery */
    private $query;

    public function __construct(CategorizedContactProfilesQuery $query)
    {
        $this->query = $query;
    }

    public function __invoke(): array
    {
        $result = ($this->query)();
        $options = [];

        foreach ($result as $row) {
            $options[$row['id']] = sprintf('%s %s [%s]', $row['lastname'], $row['firstname'], $row['category']);
        }

        return $options;
    }
}
