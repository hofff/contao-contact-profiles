<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Generator;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function count;

final class DynamicProfileProvider extends AbstractProfileProvider
{
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function name(): string
    {
        return 'dynamic';
    }

    /** {@inheritDoc} */
    public function fetchProfiles(Model $model, PageModel $pageModel, ?Specification $specification, int $offset): array
    {
        $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
        $event   = new LoadContactProfilesEvent($model, $pageModel, $sources);
        $this->eventDispatcher->dispatch($event, $event::NAME);

        if ($specification === null) {
            return $event->profiles();
        }

        $profiles = [];

        foreach ($event->profiles() as $profile) {
            if (! $specification->isSatisfiedBy($profile)) {
                continue;
            }

            $profiles[] = $profile;
        }

        return $profiles;
    }

    /** {@inheritDoc} */
    public function countTotal(Model $model, array $profiles): int
    {
        return count($profiles);
    }

    /** {@inheritDoc} */
    protected function fetchInitials(Model $model, PageModel $pageModel): Generator
    {
        $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
        $event   = new LoadContactProfilesEvent($model, $pageModel, $sources);
        $this->eventDispatcher->dispatch($event, $event::NAME);

        foreach ($event->profiles() as $profile) {
            if (! ($profile->lastname[0] ?? null)) {
                continue;
            }

            yield $profile->lastname[0] => 1;
        }
    }
}
