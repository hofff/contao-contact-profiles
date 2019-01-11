<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Driver\Connection;
use PDO;

final class CategorizedContactProfilesQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  p.*,
  c.title as category
FROM 
  tl_contact_profile p
INNER JOIN
  tl_contact_category c
  ON c.id = p.pid
ORDER BY 
  p.lastname, p.firstname
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /** @return string[][] */
    public function __invoke() : array
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->execute();

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
