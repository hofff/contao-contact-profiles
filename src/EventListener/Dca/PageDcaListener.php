<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Doctrine\DBAL\Connection;

final class PageDcaListener
{
    public function __construct(private readonly Connection $connection)
    {
    }

    #[AsCallback('tl_page', 'config.onsubmit')]
    public function onSubmit(DataContainer $dataContainer): void
    {
        if (! $dataContainer->activeRecord || $dataContainer->activeRecord->type !== 'contact_profile') {
            return;
        }

        $this->connection->update('tl_page', ['requireItem' => '1'], ['id' => $dataContainer->id]);
    }
}
