<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\CoreBundle\Event\PreviewUrlConvertEvent;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use PDO;
use Symfony\Component\HttpFoundation\Request;

final class PreviewUrlConvertListener
{
    private ContaoFramework $framework;

    private RepositoryManager $repositoryManager;

    private ProfileRepository $contactProfiles;

    private Connection $connection;

    public function __construct(
        ContaoFramework $framework,
        RepositoryManager $repositoryManager,
        ProfileRepository $contactProfiles,
        Connection $connection
    ) {
        $this->repositoryManager = $repositoryManager;
        $this->framework         = $framework;
        $this->contactProfiles   = $contactProfiles;
        $this->connection        = $connection;
    }

    /**
     * Adds the front end preview URL to the event.
     */
    public function __invoke(PreviewUrlConvertEvent $event): void
    {
        if (! $this->framework->isInitialized()) {
            return;
        }

        $request        = $event->getRequest();
        $contactProfile = $this->getContactProfile($request);
        if (! $contactProfile instanceof Profile) {
            return;
        }

        $detailPage = $this->getDetailPage($contactProfile);
        if ($detailPage === null) {
            return;
        }

        $event->setUrl($detailPage->getPreviewUrl('/' . $contactProfile->alias ?: $contactProfile->profileId()));
    }

    /** @return string[]|null */
    private function getContactProfile(Request $request): ?Profile
    {
        if (! $request->query->has('hofff_contact_profile')) {
            return null;
        }

        return $this->contactProfiles->find($request->query->getInt('hofff_contact_profile'));
    }

    /**
     * @psalm-suppress InvalidReturnType
     * @psalm-suppress InvalidReturnStatement
     */
    private function getDetailPage(Profile $contactProfile): ?PageModel
    {
        $repository = $this->repositoryManager->getRepository(PageModel::class);

        if ($contactProfile->jumpTo > 0) {
            return $repository->find((int) $contactProfile->jumpTo);
        }

        $statement = $this->connection->executeQuery(
            'SELECT jumpTo from tl_contact_category WHERE id = :categoryId LIMIT 0,1',
            ['categoryId' => $contactProfile->pid]
        );

        $pageId = $statement->fetch(PDO::FETCH_COLUMN);
        if ($pageId === false || $pageId < 1) {
            return null;
        }

        return $repository->find((int) $pageId);
    }
}
