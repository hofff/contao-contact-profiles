<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;

final class SocialAccountQuery
{
    private const QUERY = <<<'SQL'
SELECT 
  *
FROM 
  tl_contact_social_account
WHERE 
  id = :id
SQL;

    /** @var Connection */
    private $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return string[]
     */
    public function __invoke(int $accountId): array
    {
        return (array) $this->connection
            ->executeQuery(self::QUERY, ['id' => $accountId])
            ->fetchAssociative();
    }
}
