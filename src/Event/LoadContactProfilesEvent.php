<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Event;

use Contao\ContentElement;
use Contao\Module;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

use function in_array;

final class LoadContactProfilesEvent extends Event
{
    public const NAME = 'hofff.contao_contact_profile.load_contact_profiles';

    /** @var Module|ContentElement */
    private $context;

    /** @var PageModel */
    private $page;

    /** @var Profile[] */
    private $profiles = [];

    /** @var string[] */
    private $sources;

    /**
     * @param ContentElement|Module $context
     * @param string[]              $sources
     */
    public function __construct($context, PageModel $page, array $sources = [])
    {
        $this->context = $context;
        $this->page    = $page;
        $this->sources = $sources;
    }

    /** @param Profile[] $profiles */
    public function setProfiles(array $profiles): void
    {
        $this->profiles = $profiles;
    }

    /** @return Module|ContentElement */
    public function context()
    {
        return $this->context;
    }

    public function page(): PageModel
    {
        return $this->page;
    }

    /** @return Profile[] */
    public function profiles(): array
    {
        return $this->profiles;
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
}
