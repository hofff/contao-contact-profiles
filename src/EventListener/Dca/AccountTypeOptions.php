<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Doctrine\DBAL\Connection;
use PDO;

final class AccountTypeOptions
{
    private const QUERY = <<<'SQL'
SELECT 
  id,name
FROM
  tl_contact_social_account
ORDER BY name
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /** @return string[] */
    public function __invoke() : iterable
    {
        $statement = $this->connection->executeQuery(self::QUERY);
        $options   = [];

        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $options[$row['id']] = $row['name'];
        }

        return $options;
    }
}
