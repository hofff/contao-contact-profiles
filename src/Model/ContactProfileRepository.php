<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

final class ContactProfileRepository
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchPublishedByCategories(
        array $categoryIds,
        int $limit = 0,
        int $offset = 0,
        string $order = null
    ): array {
        return $this->createFetchPublishedQuery($limit, $offset, $order)
            ->andWhere('p.pid IN(:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchPublishedByProfileIds(
        array $profileIds,
        int $limit = 0,
        int $offset = 0,
        ?string $order = null
    ): array {
        $builder = $this->createFetchPublishedQuery($limit, $offset, $order)
            ->andWhere('p.id IN(:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY);

        if ($order === null) {
            $builder->orderBy('FIELD(id, :profileIds)');
        }

        return $builder
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchPublishedByIdOrAlias(string $aliasOrId): ?array
    {
        return $this->createFetchPublishedQuery()
            ->andWhere('p.id = :alias OR p.alias = :alias')
            ->setParameter('alias', $aliasOrId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC);
    }

    public function countPublishedByCategories(array $categoryIds): int
    {
        return (int) $this->createCountPublishedQuery()
            ->andWhere('p.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
    }

    public function countPublishedByProfileIds(array $profileIds): int
    {
        return (int) $this->createCountPublishedQuery()
            ->andWhere('p.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetch(PDO::FETCH_COLUMN);
    }

    private function createFetchPublishedQuery(
        int $limit = 0,
        int $offset = 0,
        string $order = null
    ): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('tl_contact_profile', 'p')
            ->where('p.published = :published')
            ->setParameter('published', '1');

        if ($limit > 0) {
            $builder->setMaxResults($limit);
        }

        if ($offset > 0) {
            $builder->setFirstResult($offset);
        }

        if ($order !== null) {
            $builder->orderBy($order);
        }

        return $builder;
    }

    private function createCountPublishedQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('count(p.id)')
            ->from('tl_contact_profile', 'p')
            ->where('p.published = :published')
            ->setParameter('published', '1');
    }
}
