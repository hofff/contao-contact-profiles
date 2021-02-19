<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Hook;

use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\PageModel;
use Doctrine\DBAL\Connection;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;

final class InsertTagsListener
{
    /** @var ContactProfileRepository */
    private $repository;

    /** @var ContaoFramework */
    private $framework;

    /** @var Connection */
    private $connection;

    public function __construct(ContactProfileRepository $repository, Connection $connection, ContaoFramework $framework)
    {
        $this->repository = $repository;
        $this->framework  = $framework;
        $this->connection = $connection;
    }

    public function __invoke(string $tag, bool $useCache, $cacheValue, array $flags)
    {
        $elements = explode('::', $tag, 2);

        if ($elements[0] !== 'contact_profile_url') {
            return false;
        }

        $profile = $this->repository->fetchById($elements[1]);
        if (!$profile) {
            return '';
        }

        $pageModelAdapter = $pageModel = $this->framework->getAdapter(PageModel::class);

        if ($profile['jumpTo'] > 0) {
            $pageModel = $pageModelAdapter->findByPk($profile['jumpTo']);
            if ($pageModel) {
                return $pageModel->getFrontendUrl();
            }
        }

        $statement = $this->connection->executeQuery(
            'SELECT jumpTo FROM tl_contact_category WHERE id=:id LIMIT 0,1',
            ['id' => $profile['pid']]
        );

        if ($statement->rowCount() === 0) {
            return '';
        }

        $pageId = $statement->fetch(\PDO::FETCH_COLUMN);
        $pageModel = $pageModelAdapter->findByPk($pageId);
        if ($pageModel) {
            return $pageModel->getFrontendUrl('/' . $profile['alias'] ?: $profile['id']);
        }

        return '';
    }
}
