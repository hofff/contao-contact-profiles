<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Event;

use Contao\Model;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

use function array_values;
use function in_array;

final class LoadContactProfilesEvent extends Event
{
    public const NAME = 'hofff.contao_contact_profile.load_contact_profiles';

    private Model $context;

    private PageModel $page;

    /** @var array<int,Profile> */
    private array $profiles = [];

    /** @var string[] */
    private array $sources;

    /**
     * @param string[] $sources
     */
    public function __construct(Model $context, PageModel $page, array $sources = [])
    {
        $this->context = $context;
        $this->page    = $page;
        $this->sources = $sources;
    }

    /** @param iterable<Profile> $profiles */
    public function setProfiles(iterable $profiles): void
    {
        $this->profiles = [];

        foreach ($profiles as $profile) {
            $this->addProfile($profile);
        }
    }

    public function context(): Model
    {
        return $this->context;
    }

    public function page(): PageModel
    {
        return $this->page;
    }

    /** @return list<Profile> */
    public function profiles(): array
    {
        return array_values($this->profiles);
    }

    /** @return list<string> */
    public function sources(): array
    {
        return $this->sources;
    }

    public function hasSource(string $source): bool
    {
        return in_array($source, $this->sources, true);
    }

    public function addProfile(Profile $profile): void
    {
        $this->profiles[$profile->profileId()] = $profile;
    }
}
