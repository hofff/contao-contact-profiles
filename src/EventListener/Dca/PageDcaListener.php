<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

final class PageDcaListener
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /** @Callback(table="tl_page", target="config.onsubmit") */
    public function onSubmit(DataContainer $dataContainer): void
    {
        if (! $dataContainer->activeRecord || $dataContainer->activeRecord->type !== 'contact_profile') {
            return;
        }

        $this->connection->update('tl_page', ['requireItem' => '1'], ['id' => $dataContainer->id]);
    }
}
