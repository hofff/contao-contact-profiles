<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\ContentModel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Generator;

use function array_fill_keys;
use function iconv;
use function range;

abstract class AbstractProfileProvider implements ProfileProvider
{
    public function supports(Model $model): bool
    {
        if (! $model instanceof ContentModel && ! $model instanceof ModuleModel) {
            return false;
        }

        return $model->hofff_contact_source === $this->name();
    }

    /** {@inheritDoc} */
    public function calculateInitials(Model $model, PageModel $pageModel): array
    {
        $letters = array_fill_keys(range('a', 'z'), 0);
        $numeric = 0;

        foreach ($this->fetchInitials($model, $pageModel) as $initial => $count) {
            $normalized = iconv('UTF-8', 'ASCII//TRANSLIT', $initial);

            if (isset($letters[$normalized])) {
                $letters[$normalized] += $count;
            } else {
                $numeric += $count;
            }
        }

        if ($numeric > 0) {
            $letters['numeric'] = $numeric;
        }

        return $letters;
    }

    /** @return Generator<string, int> */
    abstract protected function fetchInitials(Model $model, PageModel $pageModel): Generator;

    /** @return array<string,mixed> */
    protected function fetchProfilesOptions(Model $model, int $offset): array
    {
        /** @psalm-suppress RedundantCastGivenDocblockType */
        $options = [
            'order'  => $model->hofff_contact_profiles_order_sql ?: null,
            'offset' => $offset,
            'limit'  => (int) $model->numberOfItems,
        ];

        /** @psalm-suppress RedundantCastGivenDocblockType */
        $perPage = (int) $model->perPage;
        if ($perPage > 0) {
            $options['limit'] = $perPage;
        }

        return $options;
    }
}
