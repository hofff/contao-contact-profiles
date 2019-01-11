<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Event;

use Contao\ContentElement;
use Contao\Module;
use Contao\PageModel;
use Symfony\Component\EventDispatcher\Event;

final class LoadContactProfilesEvent extends Event
{
    public const NAME = 'hofff.contao_contact_profile.load_contact_profiles';

    /** @var Module|ContentElement */
    private $context;

    /** @var PageModel */
    private $page;

    /** @var string[][] */
    private $profiles = [];

    /**
     * @param ContentElement|Module $context
     */
    public function __construct($context, PageModel $page)
    {
        $this->context = $context;
        $this->page    = $page;
    }

    /** @param string[][] $profiles */
    public function setProfiles(array $profiles) : void
    {
        $this->profiles = $profiles;
    }

    /** @return Module|ContentElement */
    public function context()
    {
        return $this->context;
    }

    public function page() : PageModel
    {
        return $this->page;
    }

    /** @return string[][] */
    public function profiles() : array
    {
        return $this->profiles;
    }
}
