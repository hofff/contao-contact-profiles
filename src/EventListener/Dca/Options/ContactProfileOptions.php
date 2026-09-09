<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\EventListener\Dca\Options;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\Model\Collection;
use Hofff\Contao\ContactProfiles\Model\Category\Category;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;

use function sprintf;

#[AsCallback('tl_calendar_events', 'fields.hofff_contact_profiles.options')]
#[AsCallback('tl_content', 'fields.hofff_contact_profiles.options')]
#[AsCallback('tl_faq', 'fields.hofff_contact_profiles.options')]
#[AsCallback('tl_module', 'fields.hofff_contact_profiles.options')]
#[AsCallback('tl_news', 'fields.hofff_contact_profiles.options')]
#[AsCallback('tl_news_category', 'fields.hofff_contact_profiles.options')]
final class ContactProfileOptions
{
    public function __construct(private ProfileRepository $profiles)
    {
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
