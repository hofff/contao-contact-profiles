<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

abstract class AbstractContactProfileMigration extends AbstractMigration
{
    /** @var Connection */
    private $connection;

    /** @var string[] */
    private $sources;

    /** @var string */
    private $table;

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
        if ($schemaManager === null) {
            return false;
        }

        if (!$schemaManager->tablesExist($this->table)) {
            return false;
        }

        $columns = $schemaManager->listTableColumns($this->table);
        if (!isset($columns['hofff_contact_dynamic'])) {
            return false;
        }

        $statement = $this->connection->executeQuery(
            'SELECT count(id) FROM ' . $this->table . ' WHERE type=:type AND hofff_contact_dynamic=:dynamic',
            [
                'type'    => 'hofff_contact_profile',
                'dynamic' => '1',
            ]
        );

        return $statement->fetch(\PDO::FETCH_COLUMN) > 0;
    }

    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->getSchemaManager();
        if ($schemaManager !== null) {
            $columns = $schemaManager->listTableColumns($this->table);

            if (!isset($columns['hofff_contact_source'])) {
                $this->connection->executeStatement('ALTER TABLE ' . $this->table . ' ADD hofff_contact_source char(16) NOT NULL DEFAULT \'custom\'');
            }
            if (!isset($columns['hofff_contact_sources'])) {
                $this->connection->executeStatement('ALTER TABLE ' . $this->table . ' ADD hofff_contact_sources TINYBLOB null');
            }
        }

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

        return $this->createResult(true);
    }
}
