<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Event;

use Contao\Model;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Symfony\Contracts\EventDispatcher\Event;

final class ProfileDetailPageEvent extends Event
{
    public const NAME = 'hofff.contao_contact_profile.profile_detail_page';

    private Profile $profile;

    private Model $configuration;

    public function __construct(Profile $profile, Model $configuration)
    {
        $this->profile       = $profile;
        $this->configuration = $configuration;
    }

    public function profile(): Profile
    {
        return $this->profile;
    }

    public function configuration(): Model
    {
        return $this->configuration;
    }
}
