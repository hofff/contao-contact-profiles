<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;
use PDO;

final class PublishedContactProfileQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  *
FROM 
  tl_contact_profile
WHERE 
  (alias = :alias OR id = :alias)
  AND published='1'
LIMIT 0,1
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[][]
     */
    public function __invoke(string $aliasOrId) : ?array
    {
        if ($aliasOrId === '') {
            return [];
        }

        $statement = $this->connection->executeQuery(self::QUERY, ['alias' => $aliasOrId]);
        if ($statement->rowCount() === 0) {
            return null;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
