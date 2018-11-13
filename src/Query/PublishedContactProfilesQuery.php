<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;
use PDO;

final class PublishedContactProfilesQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  *
FROM 
  tl_contact_profile
WHERE 
  id IN(:ids)
  AND published='1'
  ORDER BY FIELD(id, :ids)
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function __invoke(array $profileIds): array
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->bindValue('ids', $profileIds);

        return $statement->fetchAll(PDO::FETCH_OBJ);
    }
}
