<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use PDO;
use Symfony\Component\HttpFoundation\Request;

final class PreviewUrlConvertListener
{
    /** @var ContaoFramework */
    private $framework;

    /** @var ContactProfileRepository */
    private $contactProfiles;

    /** @var Connection */
    private $connection;

    public function __construct(
        ContaoFramework $framework,
        ContactProfileRepository $contactProfiles,
        Connection $connection
    ) {
        $this->framework       = $framework;
        $this->contactProfiles = $contactProfiles;
        $this->connection      = $connection;
    }

    /**
     * Adds the front end preview URL to the event.
     */
    public function __invoke(PreviewUrlConvertEvent $event): void
    {
        if (! $this->framework->isInitialized()) {
            return;
        }

        $request = $event->getRequest();
        if ($request === null) {
            return;
        }

        $contactProfile = $this->getContactProfile($request);
        if ($contactProfile === null) {
            return;
        }

        $detailPage = $this->getDetailPage($contactProfile);
        if ($detailPage === null) {
            return;
        }

        $event->setUrl($detailPage->getPreviewUrl('/' . $contactProfile['alias'] ?: $contactProfile['id']));
    }

    /** @return string[]|null */
    private function getContactProfile(Request $request): ?array
    {
        if (! $request->query->has('hofff_contact_profile')) {
            return null;
        }

        return $this->contactProfiles->fetchById($request->query->get('hofff_contact_profile'));
    }

    /** @param string[] $contactProfile */
    private function getDetailPage(array $contactProfile): ?PageModel
    {
        /** @var Adapter<PageModel> $adapter */
        $adapter = $this->framework->getAdapter(PageModel::class);

        if ($contactProfile['jumpTo'] > 0) {
            return $adapter->findByPk($contactProfile['jumpTo']);
        }

        $statement = $this->connection->executeQuery(
            'SELECT jumpTo from tl_contact_category WHERE id = :categoryId LIMIT 0,1',
            ['categoryId' => $contactProfile['pid']]
        );

        $pageId = $statement->fetch(PDO::FETCH_COLUMN);
        if ($pageId === false || $pageId < 1) {
            return null;
        }

        return $adapter->findByPk($pageId);
    }
}
