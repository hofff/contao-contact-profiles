<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;

/**
 * @deprecated
 *
 * @SuppressWarnings(PHPMD.LongClassName)
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
        $result = $this->connection->executeQuery(
            self::QUERY,
            [$categoryIds],
            [Connection::PARAM_STR_ARRAY]
        );

        return $result->fetchAllAssociative();
    }

    /** @param list<string> $categoryIds */
    public function count(array $categoryIds): int
    {
        $result = $this->connection->executeQuery(
            self::COUNT_QUERY,
            [$categoryIds],
            [Connection::PARAM_STR_ARRAY]
        );

        return (int) $result->fetchOne();
    }
}
