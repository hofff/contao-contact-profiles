<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Generator;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;
use Override;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function count;

final class DynamicProfileProvider extends AbstractProfileProvider
{
    public function __construct(private EventDispatcherInterface $eventDispatcher)
    {
    }

    #[Override]
    public function name(): string
    {
        return 'dynamic';
    }

    /** {@inheritDoc} */
    #[Override]
    public function fetchProfiles(
        Model $model,
        PageModel $pageModel,
        Specification|null $specification,
        int $offset,
    ): array {
        $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
        $sources = ListUtil::toStringList($sources);
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
    #[Override]
    public function countTotal(Model $model, array $profiles): int
    {
        return count($profiles);
    }

    /** {@inheritDoc} */
    #[Override]
    protected function fetchInitials(Model $model, PageModel $pageModel): Generator
    {
        $sources = StringUtil::deserialize($model->hofff_contact_sources, true);
        $sources = ListUtil::toStringList($sources);
        $event   = new LoadContactProfilesEvent($model, $pageModel, $sources);
        $this->eventDispatcher->dispatch($event, $event::NAME);

        foreach ($event->profiles() as $profile) {
            /** @psalm-suppress RiskyTruthyFalsyComparison */
            if (! ($profile->lastname[0] ?? null)) {
                continue;
            }

            yield $profile->lastname[0] => 1;
        }
    }
}
