<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Contao\StringUtil;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

use function is_numeric;
use function preg_match;

final class ContactProfileRepository
{
    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function fetchById($profileId)
    {
        $statement = $this->connection->executeQuery(
            'SELECT * FROM tl_contact_profile WHERE id=:id LIMIT 0,1',
            ['id' => $profileId]
        );

        if ($statement->rowCount() === 0) {
            return null;
        }

        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    public function fetchPublishedByCategories(
        array $categoryIds,
        int $limit = 0,
        int $offset = 0,
        string $order = null,
        array $criteria = []
    ): array {
        return $this->createFetchPublishedQuery($limit, $offset, $order, $criteria)
            ->andWhere('p.pid IN(:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchPublishedByProfileIds(
        array $profileIds,
        int $limit = 0,
        int $offset = 0,
        ?string $order = null,
        array $criteria = []
    ): array {
        $builder = $this->createFetchPublishedQuery($limit, $offset, $order, $criteria)
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
        $field = is_numeric($aliasOrId) ? 'id' : 'alias';

        return $this->createFetchPublishedQuery()
            ->andWhere('p.' . $field . ' = :alias')
            ->setParameter('alias', $aliasOrId, PDO::PARAM_STR)
            ->setMaxResults(1)
            ->execute()
            ->fetch(PDO::FETCH_ASSOC) ?: null;
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

    public function fetchInitialsOfPublishedByCategories(array $categoryIds): array
    {
        return $this->createFetchPublishedInitialsQuery()
            ->andWhere('p.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchInitialsOfPublishedByProfileIds(array $profileIds): array
    {
        return $this->createFetchPublishedInitialsQuery()
            ->andWhere('p.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    private function createFetchPublishedQuery(
        int $limit = 0,
        int $offset = 0,
        string $order = null,
        array $criteria = []
    ): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('tl_contact_profile', 'p')
            ->where('p.published = :published')
            ->setParameter('published', '1');

        foreach ($criteria as $criterion => $parameters) {
            $builder->andWhere($criterion);
            foreach ($parameters as $parameter => $value) {
                $builder->setParameter($parameter, $value);
            }
        }

        if ($limit > 0) {
            $builder->setMaxResults($limit);
        }

        if ($offset > 0) {
            $builder->setFirstResult($offset);
        }

        if ($order !== null) {
            $builder->addOrderBy($order, ' ');
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

    private function createFetchPublishedInitialsQuery(): QueryBuilder
    {
        return $this->connection->createQueryBuilder()
            ->select('LOWER(SUBSTR(p.lastname, 1, 1)) as letter, count(p.id) AS count')
            ->from('tl_contact_profile', 'p')
            ->where('p.published = :published')
            ->groupBy('letter')
            ->setParameter('published', '1');
    }
}
