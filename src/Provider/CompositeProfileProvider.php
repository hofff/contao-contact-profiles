<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Provider;

use Contao\ContentModel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\PageModel;
use Netzmacht\Contao\Toolkit\Data\Model\Specification;
use RuntimeException;

use function get_class;

final class CompositeProfileProvider implements ProfileProvider
{
    /** @var array<string,ProfileProvider> */
    private array $providers = [];

    /**
     * @param ProfileProvider[] $providers
     */
    public function __construct(iterable $providers)
    {
        foreach ($providers as $provider) {
            $this->providers[$provider->name()] = $provider;
        }
    }

    public function name(): string
    {
        return 'composite';
    }

    public function supports(Model $model): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->supports($model)) {
                return true;
            }
        }

        return false;
    }

    /** {@inheritDoc} */
    public function fetchProfiles(Model $model, PageModel $pageModel, Specification $specification, int $offset): array
    {
        return $this->provider($model)->fetchProfiles($model, $pageModel, $specification, $offset);
    }

    /** {@inheritDoc} */
    public function countTotal(Model $model, array $profiles): int
    {
        return $this->provider($model)->countTotal($model, $profiles);
    }

    /** {@inheritDoc} */
    public function calculateInitials(Model $model, PageModel $pageModel): array
    {
        return $this->provider($model)->calculateInitials($model, $pageModel);
    }

    private function provider(Model $model): ProfileProvider
    {
        if (! $model instanceof ContentModel && ! $model instanceof ModuleModel) {
            throw new RuntimeException('Unsupported model context: ' . get_class($model));
        }

        foreach ($this->providers as $provider) {
            if ($provider->supports($model)) {
                return $provider;
            }
        }

        throw new RuntimeException('No supported provider found');
    }
}
