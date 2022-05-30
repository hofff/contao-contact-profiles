<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\Model;
use Contao\PageModel;
use Contao\StringUtil;
use Generator;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;

final class CustomProviderProvider extends AbstractProfileProvider
{
    private ProfileRepository $profiles;

    public function __construct(ProfileRepository $profiles)
    {
        $this->profiles = $profiles;
    }

    public function name(): string
    {
        return 'custom';
    }

    /**
     * {@inheritDoc}
     *
     * @psalm-suppress MoreSpecificReturnType
     */
    public function fetchProfiles(Model $model, PageModel $pageModel, Specification $specification, int $offset): array
    {
        $options = $this->fetchProfilesOptions($model, $offset);
        if ($options['order'] === null) {
            $order            = StringUtil::deserialize($model->hofff_contact_profiles_order, true);
            $options['order'] = QueryUtil::orderByIds('id', $order);
        }

        $profileIds = StringUtil::deserialize($model->hofff_contact_profiles, true);
        $profiles   = $this->profiles->fetchPublishedByProfileIdsAndSpecification(
            $profileIds,
            $specification,
            $options
        );

        /** @psalm-suppress LessSpecificReturnStatement */
        return $profiles ? $profiles->getModels() : [];
    }

    /** {@inheritDoc} */
    public function countTotal(Model $model, array $profiles): int
    {
        $profileIds = StringUtil::deserialize($model->hofff_contact_profiles, true);

        return $this->profiles->countPublishedByProfileIds($profileIds);
    }

    /** {@inheritDoc} */
    protected function fetchInitials(Model $model, PageModel $pageModel): Generator
    {
        $profileIds = StringUtil::deserialize($model->hofff_contact_profiles, true);

        foreach ($this->profiles->fetchInitialsOfPublishedByProfileIds($profileIds) as $row) {
            yield $row['letter'] => (int) $row['count'];
        }
    }
}
