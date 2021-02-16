<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * @deprecated
 */
final class PublishedContactProfilesQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  *
FROM 
  tl_contact_profile
WHERE 
  id IN (?)
  AND published='1'
  ORDER BY FIELD(id, ?)
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $profileIds
     *
     * @return string[][]
     */
    public function __invoke(array $profileIds) : array
    {
        $statement = $this->connection->executeQuery(
            self::QUERY,
            [$profileIds, $profileIds],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }
}
