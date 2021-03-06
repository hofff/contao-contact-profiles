<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFrameworkInterface;
use Contao\Input;
use Contao\NewsArchiveModel;
use Contao\NewsModel;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Query\PublishedContactProfilesQuery;

final class NewsContactProfilesListener
{
    /** @var ContaoFrameworkInterface */
    private $framework;

    /** @var PublishedContactProfilesQuery */
    private $query;

    public function __construct(ContaoFrameworkInterface $framework, PublishedContactProfilesQuery $query)
    {
        $this->framework = $framework;
        $this->query     = $query;
    }

    public function onLoadContactProfiles(LoadContactProfilesEvent $event) : void
    {
        $news = $this->getNews();
        if (! $news) {
            return;
        }

        $profileIds = StringUtil::deserialize($news->hofff_contact_profiles, true);
        $profiles   = ($this->query)($profileIds);

        $event->setProfiles($profiles);
    }

    private function getNews() : ?NewsModel
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

    private function getNewsAlias() : ?string
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

    private function getNewsArchive() : ?NewsArchiveModel
    {
        $repository = $this->framework->getAdapter(NewsArchiveModel::class);

        return $repository->__call('findOneByJumpTo', [$GLOBALS['objPage']->id]);
    }
}
