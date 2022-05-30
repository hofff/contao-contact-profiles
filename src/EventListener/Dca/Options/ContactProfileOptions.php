<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\ServiceAnnotation\Callback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\Category\Category;
use Hofff\Contao\ContactProfiles\Model\Profile\Profile;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;

use function assert;
use function sprintf;

/**
 * @Callback(table="tl_calendar_events", target="fields.hofff_contact_profiles.options")
 * @Callback(table="tl_content", target="fields.hofff_contact_profiles.options")
 * @Callback(table="tl_faq", target="fields.hofff_contact_profiles.options")
 * @Callback(table="tl_module", target="fields.hofff_contact_profiles.options")
 * @Callback(table="tl_news", target="fields.hofff_contact_profiles.options")
 * @Callback(table="tl_news_category", target="fields.hofff_contact_profiles.options")
 */
final class ContactProfileOptions
{
    private ProfileRepository $profiles;

    public function __construct(ProfileRepository $profiles)
    {
        $this->profiles = $profiles;
    }

    /**
     * @return string[]
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(): array
    {
        $collection = $this->profiles->findAll(['language' => $GLOBALS['TL_LANGUAGE']]);
        $options    = [];
        if (! $collection instanceof Collection) {
            return $options;
        }

        foreach ($collection as $profile) {
            assert($profile instanceof Profile);
            $category = $profile->getRelated('pid');

            /** @psalm-suppress DocblockTypeContradiction */
            $options[$profile->profileId()] = sprintf(
                '%s %s [%s]',
                $profile->lastname,
                $profile->firstname,
                $category instanceof Category ? $category->title : $profile->pid,
            );
        }

        return $options;
    }
}
