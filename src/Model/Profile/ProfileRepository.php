<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Model\Profile;

use Contao\CoreBundle\Security\Authentication\Token\TokenChecker;
use Contao\Model\Collection;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Netzmacht\Contao\Toolkit\Data\Model\ContaoRepository;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;
use Terminal42\DcMultilingualBundle\QueryBuilder\MultilingualQueryBuilderInterface;

use function count;
use function is_string;
use function str_repeat;
use function str_replace;

/**
 * @extends ContaoRepository<Profile>
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
final class ProfileRepository extends ContaoRepository
{
    private Connection $connection;

    private TokenChecker $tokenChecker;

    /** @psalm-param class-string<Profile> $modelClass */
    public function __construct(string $modelClass, Connection $connection, TokenChecker $tokenChecker)
    {
        parent::__construct($modelClass);

        $this->tokenChecker = $tokenChecker;
        $this->connection   = $connection;
    }

    /** @param list<string|int> $categoryIds */
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

    /** @param list<string|int> $profileIds */
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

    /**
     * @param list<string|int>    $profileIds
     * @param array<string,mixed> $options
     */
    public function fetchPublishedByProfileIds(array $profileIds, array $options = []): ?Collection
    {
        if ($profileIds === []) {
            return null;
        }

        $columns = ['.id IN(?' . str_repeat(',?', count($profileIds) - 1) . ')'];
        $values  = $profileIds;
        $this->addPublishedCondition($columns, $values);

        return $this->findBy($columns, $values, $options);
    }

    /**
     * @param list<string|int>    $profileIds
     * @param array<string,mixed> $options
     */
    public function fetchPublishedByProfileIdsAndSpecification(
        array $profileIds,
        Specification $specification,
        array $options = []
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

    /**
     * @param list<string|int>    $categoryIds
     * @param array<string,mixed> $options
     */
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

    /**
     * @param list<string|int>    $categoryIds
     * @param array<string,mixed> $options
     */
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

    /**
     * @param array<string,mixed> $options
     */
    public function fetchPublishedByIdOrAlias(string $identifier, array $options = []): ?Profile
    {
        if ($this->isMultilingual()) {
            $columns = ['( IFNULL(translation.alias, .alias)=? ) OR .id=? '];
            $values  = [$identifier, $identifier];
        } else {
            $columns = ['( .id=? OR .alias=?)'];
            $values  = [$identifier, $identifier];
        }

        $this->addPublishedCondition($columns, $values);

        return $this->findOneBy($columns, $values, $options);
    }

    /**
     * @param list<string|int> $categoryIds
     *
     * @return list<array<string,mixed>>
     */
    public function fetchInitialsOfPublishedByCategories(array $categoryIds): array
    {
        $result = $this->createFetchPublishedInitialsQuery()
            ->andWhere($this->getTableName() . '.pid IN (:categoryIds)')
            ->setParameter('categoryIds', $categoryIds, ArrayParameterType::STRING)
            ->executeQuery();

        return $result->fetchAllAssociative();
    }

    /**
     * @param list<string|int> $profileIds
     *
     * @return list<array<string,mixed>>
     */
    public function fetchInitialsOfPublishedByProfileIds(array $profileIds): array
    {
        $result = $this->createFetchPublishedInitialsQuery()
            ->andWhere($this->getTableName() . '.id IN (:profileIds)')
            ->setParameter('profileIds', $profileIds, ArrayParameterType::STRING)
            ->executeQuery();

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

    /**
     * @param list<string> $columns
     * @param list<mixed>  $values
     */
    private function addPublishedCondition(array &$columns, array &$values): void
    {
        if ($this->tokenChecker->isPreviewMode()) {
            return;
        }

        $columns[] = '.published=?';
        $values[]  = '1';
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    private function createFetchPublishedInitialsQuery(): QueryBuilder
    {
        if ($this->isMultilingual()) {
            /** @psalm-var MultilingualQueryBuilderInterface $multilingualBuilder */
            $multilingualBuilder = MultilingualProfile::getMultilingualQueryBuilder();
            $language            = isset($GLOBALS['TL_LANGUAGE']) && is_string($GLOBALS['TL_LANGUAGE'])
                ? str_replace('-', '_', $GLOBALS['TL_LANGUAGE'])
                : '';

            // Consider the fallback language
            $fallbackLanguage = MultilingualProfile::getFallbackLanguage();
            if ($fallbackLanguage !== null && $language === $fallbackLanguage) {
                $language = '';
            }

            $multilingualBuilder->buildQueryBuilderForFind($language);

            $queryBuilder = $multilingualBuilder->getQueryBuilder();
            $queryBuilder
                ->select(
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

        $queryBuilder->addSelect('COUNT(' . $this->getTableName() . '.id) AS count');
        $queryBuilder->groupBy('letter');

        if (! $this->tokenChecker->isPreviewMode()) {
            $queryBuilder
                ->where($this->getTableName() . '.published = :published')
                ->setParameter('published', '1');
        }

        return $queryBuilder;
    }

    private function isMultilingual(): bool
    {
        return $this->getModelClass() === MultilingualProfile::class;
    }
}
