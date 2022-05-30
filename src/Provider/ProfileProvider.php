<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\Model;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;

interface ProfileProvider
{
    public function name(): string;

    public function supports(Model $model): bool;

    /** @return list<Profile> */
    public function fetchProfiles(
        Model $model,
        PageModel $pageModel,
        ?Specification $specification,
        int $offset
    ): array;

    /** @param list<Profile> $profiles */
    public function countTotal(Model $model, array $profiles): int;

    /** @return array<string,int> */
    public function calculateInitials(Model $model, PageModel $pageModel): array;
}
