<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

use function serialize;

abstract class AbstractContactProfileMigration extends AbstractMigration
{
    private Connection $connection;

    /** @var string[] */
    private array $sources;

    private string $table;

    /** @param string[] $sources */
    public function __construct(Connection $connection, array $sources, string $table)
    {
        $this->connection = $connection;
        $this->sources    = $sources;
        $this->table      = $table;
    }

    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->getSchemaManager();
        if (! $schemaManager->tablesExist($this->table)) {
            return false;
        }

        $statement = $this->connection->executeQuery(
            'SELECT count(id) FROM ' . $this->table . ' WHERE type=:type LIMIT 0,1',
            ['type' => 'hofff_contact_profile']
        );

        if ($statement->fetchOne() > 0) {
            $statement->free();

            return true;
        }

        $statement->free();

        $columns = $schemaManager->listTableColumns($this->table);

        return isset($columns['hofff_contact_dynamic']);
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->getSchemaManager();
        $columns       = $schemaManager->listTableColumns($this->table);

        if (! isset($columns['hofff_contact_source'])) {
            $this->connection->executeStatement(
                'ALTER TABLE ' . $this->table . ' ADD hofff_contact_source char(16) NOT NULL DEFAULT \'custom\''
            );
        }

        if (! isset($columns['hofff_contact_sources'])) {
            $this->connection->executeStatement(
                'ALTER TABLE ' . $this->table . ' ADD hofff_contact_sources TINYBLOB null'
            );
        }

        $this->connection->update(
            $this->table,
            ['type' => 'hofff_contact_profile_list'],
            ['type' => 'hofff_contact_profile']
        );

        if (isset($columns['hofff_contact_dynamic'])) {
            $this->connection->update(
                $this->table,
                [
                    'hofff_contact_source'  => 'dynamic',
                    'hofff_contact_sources' => serialize($this->sources),
                ],
                [
                    'type'                  => 'hofff_contact_profile',
                    'hofff_contact_dynamic' => 1,
                ]
            );

            $this->connection->executeStatement('ALTER TABLE ' . $this->table . ' DROP hofff_contact_dynamic');
        }

        return $this->createResult(true);
    }
}
