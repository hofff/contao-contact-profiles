<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;

final class ResponsibilitiesQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  *
FROM 
  tl_contact_responsibility
WHERE 
  id IN (?)
  ORDER BY FIELD(id, ?)
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $responsibilityIds
     *
     * @return string[][]
     */
    public function __invoke(array $responsibilityIds): array
    {
        $result = $this->connection->executeQuery(
            self::QUERY,
            [$responsibilityIds, $responsibilityIds],
            [Connection::PARAM_STR_ARRAY, Connection::PARAM_STR_ARRAY]
        );

        return $result->fetchAllAssociative();
    }
}
