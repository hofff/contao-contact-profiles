<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Query;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use PDO;

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
     *
     * @throws DBALException
     */
    public function __invoke(int $accountId) : array
    {
        $statement = $this->connection->prepare(self::QUERY);
        $statement->bindValue('id', $accountId);
        $statement->execute();

        return $statement->fetch(PDO::FETCH_ASSOC);
    }
}
