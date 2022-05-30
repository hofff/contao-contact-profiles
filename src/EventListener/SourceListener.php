<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener;

use Contao\Config;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\Model;
use Contao\Model\Collection;
use Contao\StringUtil;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;

abstract class SourceListener
{
    protected ContaoFramework $framework;

    protected RepositoryManager $repositoryManager;

    protected ProfileRepository $repository;

    public function __construct(
        ContaoFramework $framework,
        RepositoryManager $repositoryManager,
        ProfileRepository $repository
    ) {
        $this->framework         = $framework;
        $this->repository        = $repository;
        $this->repositoryManager = $repositoryManager;
    }

    public function __invoke(LoadContactProfilesEvent $event): void
    {
        if (! $event->hasSource($this->source())) {
            return;
        }

        $alias = $this->getAlias();
        if (! $alias) {
            return;
        }

        $sourceModel = $this->fetchSource($alias);
        if (! $sourceModel) {
            return;
        }

        $profiles = $this->fetchProfiles($sourceModel);

        /** @psalm-suppress ArgumentTypeCoercion */
        $event->setProfiles($profiles ? $profiles->getModels() : []);
    }

    abstract protected function source(): string;

    abstract protected function fetchSource(string $alias): ?Model;

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function getAlias(): ?string
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

    protected function fetchProfiles(Model $sourceModel): ?Collection
    {
        $profileIds = StringUtil::deserialize($sourceModel->hofff_contact_profiles, true);
        $order      = StringUtil::deserialize($sourceModel->hofff_contact_profiles_order, true);

        return $this->repository->fetchPublishedByProfileIds(
            $profileIds,
            ['order' => QueryUtil::orderByIds('id', $order)]
        );
    }
}
