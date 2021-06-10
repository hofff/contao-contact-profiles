<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;
use PDO;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 * @deprecated
 */
final class PublishedContactProfilesByCategoriesQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  p.*
FROM 
  tl_contact_profile p
WHERE 
  p.pid IN (?)
  AND p.published='1'
ORDER BY 
  p.lastname, p.firstname
SQL;

    private const COUNT_QUERY = <<<'SQL'
SELECT 
  count(p.id)
FROM 
  tl_contact_profile p
WHERE 
  p.pid IN (?)
  AND p.published='1'
ORDER BY 
  p.lastname, p.firstname
SQL;


    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @param string[] $categoryIds
     *
     * @return string[][]
     */
    public function __invoke(array $categoryIds): array
    {
        $statement = $this->connection->executeQuery(
            self::QUERY,
            [$categoryIds],
            [Connection::PARAM_STR_ARRAY]
        );

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /** @param list<string> $categoryIds */
    public function count(array $categoryIds): int
    {
        $statement = $this->connection->executeQuery(
            self::COUNT_QUERY,
            [$categoryIds],
            [Connection::PARAM_STR_ARRAY]
        );

        return (int) $statement->fetch(PDO::FETCH_COLUMN);
    }
}
