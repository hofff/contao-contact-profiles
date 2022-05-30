<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca;

use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;

use function assert;
use function sprintf;

final class ContactProfileOptions
{
    private ProfileRepository $profiles;

    public function __construct(ProfileRepository $profiles)
    {
        $this->profiles = $profiles;
    }

    /** @return string[] */
    public function __invoke(): array
    {
        $collection = $this->profiles->findAll(['language' => $GLOBALS['TL_LANGUAGE']]);
        $options = [];

        foreach ($collection ?: [] as $profile) {
            assert($profile instanceof Profile);
            $options[$profile->profileId()] = sprintf(
                '%s %s [%s]',
                $profile->lastname,
                $profile->firstname,
                $profile->getRelated('pid')->title
            );
        }

        return $options;
    }
}
