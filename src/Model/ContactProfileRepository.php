<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;

use function is_int;
use function is_numeric;
use function is_string;

final class ContactProfileRepository
{
    /** @var Connection */
    private $connection;

    /** @var TokenChecker */
    private $tokenChecker;

    public function __construct(Connection $connection, TokenChecker $tokenChecker)
    {
        $this->connection   = $connection;
        $this->tokenChecker = $tokenChecker;
    }

    /**
     * @param string|int $profileId
     *
     * @return array<string,mixed>|null
     */
    public function fetchById($profileId): ?array
    {
        $result = $this->connection->executeQuery(
            'SELECT * FROM tl_contact_profile WHERE id=:id LIMIT 0,1',
            ['id' => $profileId]
        );

        if ($result->rowCount() === 0) {
            return null;
        }

        return $result->fetchAssociative() ?: null;
    }

    /**
     * @param list<string|int>                  $categoryIds
     * @param array<string,array<string,mixed>> $criteria
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchPublishedByCategories(
        array $categoryIds,
        int $limit = 0,
        int $offset = 0,
        ?string $order = null,
        array $criteria = []
    ): array {
        $result = $this->createFetchPublishedQuery($limit, $offset, $order, $criteria)
            ->andWhere('p.pid IN(:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return [];
        }

        return $result->fetchAllAssociative();
    }

    /**
     * @param list<string|int>                  $profileIds
     * @param array<string,array<string,mixed>> $criteria
     *
     * @return array<int, array<string, mixed>>
     */
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

        $result = $builder->execute();

        if (is_int($result) || is_string($result)) {
            return [];
        }

        return $result->fetchAllAssociative();
    }

    /**
     * @return array<string,mixed>|null
     */
    public function fetchPublishedByIdOrAlias(string $aliasOrId): ?array
    {
        $field  = is_numeric($aliasOrId) ? 'id' : 'alias';
        $result =  $this->createFetchPublishedQuery()
            ->andWhere('p.' . $field . ' = :alias')
            ->setParameter('alias', $aliasOrId, PDO::PARAM_STR)
            ->setMaxResults(1)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return null;
        }

        return $result->fetchAssociative() ?: null;
    }

    /**
     * @param list<string|int> $categoryIds
     */
    public function countPublishedByCategories(array $categoryIds): int
    {
        $result = $this->createCountPublishedQuery()
            ->andWhere('p.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return 0;
        }

        return (int) $result->fetchOne();
    }

    /**
     * @param list<string|int> $profileIds
     */
    public function countPublishedByProfileIds(array $profileIds): int
    {
        $result = $this->createCountPublishedQuery()
            ->andWhere('p.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return 0;
        }

        return (int) $result->fetchOne();
    }

    /**
     * @param list<string|int> $categoryIds
     *
     * @return array<int,array<string,mixed>>
     */
    public function fetchInitialsOfPublishedByCategories(array $categoryIds): array
    {
        $result = $this->createFetchPublishedInitialsQuery()
            ->andWhere('p.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return [];
        }

        return $result->fetchAllAssociative();
    }

    /**
     * @param list<string|int> $profileIds
     *
     * @return array<int,array<string,mixed>>
     */
    public function fetchInitialsOfPublishedByProfileIds(array $profileIds): array
    {
        $result = $this->createFetchPublishedInitialsQuery()
            ->andWhere('p.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        if (is_int($result) || is_string($result)) {
            return [];
        }

        return $result->fetchAllAssociative();
    }

    /**
     * @param array<string,array<string,mixed>> $criteria
     */
    private function createFetchPublishedQuery(
        int $limit = 0,
        int $offset = 0,
        ?string $order = null,
        array $criteria = []
    ): QueryBuilder {
        $builder = $this->connection->createQueryBuilder()
            ->select('p.*')
            ->from('tl_contact_profile', 'p');

        if (! $this->tokenChecker->isPreviewMode()) {
            $builder
                ->where('p.published = :published')
                ->setParameter('published', '1');
        }

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
        $builder = $this->connection->createQueryBuilder()
            ->select('count(p.id)')
            ->from('tl_contact_profile', 'p');

        if (! $this->tokenChecker->isPreviewMode()) {
            $builder
                ->where('p.published = :published')
                ->setParameter('published', '1');
        }

        return $builder;
    }

    private function createFetchPublishedInitialsQuery(): QueryBuilder
    {
        $builder = $this->connection->createQueryBuilder()
            ->select('LOWER(SUBSTR(p.lastname, 1, 1)) as letter, count(p.id) AS count')
            ->from('tl_contact_profile', 'p')
            ->groupBy('letter');

        if (! $this->tokenChecker->isPreviewMode()) {
            $builder
                ->where('p.published = :published')
                ->setParameter('published', '1');
        }

        return $builder;
    }
}
