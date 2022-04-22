<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Migration;

use Doctrine\DBAL\Connection;

final class ContactProfileModuleMigration extends AbstractContactProfileMigration
{
    /** @param string[] $sources */
    public function __construct(Connection $connection, array $sources)
    {
        parent::__construct($connection, $sources, 'tl_module');
    }
}
