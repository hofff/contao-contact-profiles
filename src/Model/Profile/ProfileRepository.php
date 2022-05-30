<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Collection;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Netzmacht\Contao\Toolkit\Data\Model\ContaoRepository;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;

use function str_repeat;
use function str_replace;

/** @extends ProfileRepository<Profile> */
final class ProfileRepository extends ContaoRepository
{
    private Connection $connection;

    private TokenChecker $tokenChecker;


    public function __construct(string $modelClass, Connection $connection, TokenChecker $tokenChecker)
    {
        parent::__construct($modelClass);

        $this->tokenChecker = $tokenChecker;
        $this->connection   = $connection;
    }

    public function countPublishedByCategories(array $categoryIds): int
    {
        if ($categoryIds === []) {
            return 0;
        }

        $columns = ['.pid IN(?' . str_repeat(',?', count($categoryIds) - 1) . ')'];
        $values  = $categoryIds;
        $this->addPublishedCondition($columns, $values);

        return $this->countBy($columns, $values);
    }

    public function countPublishedByProfileIds(array $profileIds): int
    {
        if ($profileIds === []) {
            return 0;
        }

        $columns = ['.id IN(?' . str_repeat(',?', count($profileIds) - 1) . ')'];
        $values  = $profileIds;
        $this->addPublishedCondition($columns, $values);

        return $this->countBy($columns, $values);
    }

    public function fetchPublishedByProfileIds($profileIds, array $options): ?Collection
    {
        if ($profileIds === []) {
            return null;
        }

        $columns = ['.id IN(?' . str_repeat(',?', count($profileIds) - 1) . ')'];
        $values  = $profileIds;
        $this->addPublishedCondition($columns, $values);

        return $this->findBy($columns, $values, $options);
    }

    public function fetchPublishedByProfileIdsAndSpecification(
        array $profileIds,
        Specification $specification,
        array $options
    ): ?Collection {
        if ($profileIds === []) {
            return null;
        }

        $columns = ['.id IN(?' . str_repeat(',?', count($profileIds) - 1) . ')'];
        $values  = $profileIds;
        $this->addPublishedCondition($columns, $values);
        $specification->buildQuery($columns, $values);

        return $this->findBy($columns, $values, $options);
    }

    public function fetchPublishedByCategories(array $categoryIds, array $options = []): ?Collection
    {
        if ($categoryIds === []) {
            return null;
        }

        $columns = ['.pid IN(?' . str_repeat(',?', count($categoryIds) - 1) . ')'];
        $values  = $categoryIds;
        $this->addPublishedCondition($columns, $values);

        return $this->findBy($columns, $values, $options);
    }

    public function fetchPublishedByCategoriesAndSpecification(
        array $categoryIds,
        Specification $specification,
        array $options = []
    ): ?Collection {
        if ($categoryIds === []) {
            return null;
        }

        $columns = ['.pid IN(?' . str_repeat(',?', count($categoryIds) - 1) . ')'];
        $values  = $categoryIds;
        $this->addPublishedCondition($columns, $values);
        $specification->buildQuery($columns, $values);

        return $this->findBy($columns, $values, $options);
    }

    public function fetchPublishedByIdOrAlias(string $identifier): ?Profile
    {
        if ($this->isMultilingual()) {
            $columns = ['( .id=? OR IFNULL(translation.alias, .alias)=? )'];
            $values  = [$identifier, $identifier];
        } else {
            $columns = ['( .id=? OR .alias=?)'];
            $values  = [$identifier, $identifier];
        }

        $this->addPublishedCondition($columns, $values);

        return $this->findBy($columns, $values);
    }

    public function fetchInitialsOfPublishedByCategories(array $categoryIds): array
    {
        $query  = $this->createFetchPublishedInitialsQuery();
        $result = $query
            ->andWhere($this->getTableName() . '.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        return $result->fetchAllAssociative();
    }

    public function fetchInitialsOfPublishedByProfileIds(array $profileIds): array
    {
        $query  = $this->createFetchPublishedInitialsQuery();
        $result = $query
            ->andWhere($this->getTableName() . '.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, Connection::PARAM_STR_ARRAY)
            ->execute();

        return $result->fetchAllAssociative();
    }

    public function findByNewsCategory(int $newsCategoryId): ?Collection
    {
        $result = $this->connection->executeQuery(
            'SELECT contact_profile_id FROM tl_contact_profile_news_category WHERE news_category_id=:id',
            ['id' => $newsCategoryId]
        );

        return $this->findMultipleByIds($result->fetchFirstColumn());
    }

    private function addPublishedCondition(array &$columns, array &$values): void
    {
        if ($this->tokenChecker->isPreviewMode()) {
            return;
        }

        $columns[] = '.published=?';
        $values[]  = '1';
    }

    private function createFetchPublishedInitialsQuery(): QueryBuilder
    {
        if ($this->isMultilingual()) {
            $multilingualQueryBuilder = MultilingualProfile::getMultilingualQueryBuilder();
            $language                 = str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);

            // Consider the fallback language
            $fallbackLanguage = MultilingualProfile::getFallbackLanguage();
            if (null !== $fallbackLanguage && $fallbackLanguage === $language) {
                $language = '';
            }

            $multilingualQueryBuilder->buildQueryBuilderForFind($language);

            $queryBuilder = $multilingualQueryBuilder->getQueryBuilder();
            $queryBuilder
                ->addSelect(
                    'LOWER(
                      SUBSTR(
                        IFNULL(translation.lastname, ' . $this->getTableName() . '.lastname), 1, 1)
                    ) as letter'
                );
        } else {
            $queryBuilder = $this->connection->createQueryBuilder();
            $queryBuilder
                ->select('LOWER(SUBSTR(' . $this->getTableName() . '.lastname, 1, 1)) as letter')
                ->from($this->getTableName());
        }

        $queryBuilder->addSelect('COUNT(' . $this->getTableName()  . '.id) AS count');
        $queryBuilder->groupBy('letter');

        if (! $this->tokenChecker->isPreviewMode()) {
            $queryBuilder
                ->where($this->getTableName() . '.published = :published')
                ->setParameter('published', '1');
        }

        return $queryBuilder;
    }

    /**
     * @return bool
     */
    private function isMultilingual(): bool
    {
        return $this->getModelClass() === MultilingualProfile::class;
    }
}
