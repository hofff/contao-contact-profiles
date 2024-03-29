<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\ContactProfileRepository;
use Hofff\Contao\ContactProfiles\Util\ContactProfileUtil;

use function in_array;

final class NewsContactProfilesListener
{
    /** @var ContaoFramework */
    private $framework;

    /** @var ContactProfileRepository */
    private $repository;

    public function __construct(ContaoFramework $framework, ContactProfileRepository $repository)
    {
        $this->framework  = $framework;
        $this->repository = $repository;
    }

    public function onLoadContactProfiles(LoadContactProfilesEvent $event): void
    {
        if (! in_array('news', $event->sources(), true)) {
            return;
        }

        $news = $this->getNews();
        if (! $news) {
            return;
        }

        $profileIds = StringUtil::deserialize($news->hofff_contact_profiles, true);
        $order      = StringUtil::deserialize($news->hofff_contact_profiles_order, true);
        $profiles   = $this->repository->fetchPublishedByProfileIds($profileIds);
        $profiles   = ContactProfileUtil::orderListByIds($profiles, $order);

        $event->setProfiles($profiles);
    }

    private function getNews(): ?NewsModel
    {
        $newsAlias = $this->getNewsAlias();
        if (! $newsAlias) {
            return null;
        }

        $newsArchive = $this->getNewsArchive();
        if (! $newsArchive) {
            return null;
        }

        $repository = $this->framework->getAdapter(NewsModel::class);

        return $repository->__call('findPublishedByParentAndIdOrAlias', [$newsAlias, [$newsArchive->id]]);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getNewsAlias(): ?string
    {
        if (! isset($GLOBALS['objPage'])) {
            return null;
        }

        $inputAdapter  = $this->framework->getAdapter(Input::class);
        $configAdapter = $this->framework->getAdapter(Config::class);

        if ($configAdapter->__call('get', ['useAutoItem'])) {
            return $inputAdapter->__call('get', ['auto_item']);
        }

        return $inputAdapter->__call('get', ['items']);
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getNewsArchive(): ?NewsArchiveModel
    {
        $repository = $this->framework->getAdapter(NewsArchiveModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
