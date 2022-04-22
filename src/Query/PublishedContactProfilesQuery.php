<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;

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
    public function __invoke(array $profileIds): array
    {
        $result = $this->connection->executeQuery(
            self::QUERY,
            [$profileIds, $profileIds],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        return $result->fetchAllAssociative();
    }
}
