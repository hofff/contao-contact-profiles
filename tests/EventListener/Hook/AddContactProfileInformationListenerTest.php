<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\EventListener\Hook;

use Contao\FrontendTemplate;
use Hofff\Contao\ContactProfiles\EventListener\Hook\AddContactProfileInformationListener;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Tests\Fixtures\RecordingProfile;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function serialize;

#[CoversClass(AddContactProfileInformationListener::class)]
#[UsesClass(ListUtil::class)]
#[UsesClass(QueryUtil::class)]
#[UsesClass(ProfileRepository::class)]
final class AddContactProfileInformationListenerTest extends TestCase
{
    use CreateObjectsTrait;

    public function testFetchesProfilesAsIntegersInStoredOrder(): void
    {
        $listener = new AddContactProfileInformationListener($this->createProfileRepository(), ['news_']);

        $listener($this->createTemplate(FrontendTemplate::class, 'news_full', [
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => '',
        ]));

        self::assertCount(1, RecordingProfile::$calls);
        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('FIELD( tl_contact_profile.id,3,1,2)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testFetchesProfilesInConfiguredOrder(): void
    {
        $listener = new AddContactProfileInformationListener($this->createProfileRepository(), ['news_']);

        $listener($this->createTemplate(FrontendTemplate::class, 'news_full', [
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => serialize(['2', '3', '1']),
        ]));

        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('FIELD( tl_contact_profile.id,2,3,1)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testIgnoresNotMatchingTemplates(): void
    {
        $listener = new AddContactProfileInformationListener($this->createProfileRepository(), ['news_']);

        $listener($this->createTemplate(FrontendTemplate::class, 'event_full', [
            'hofff_contact_profiles'       => serialize(['3']),
            'hofff_contact_profiles_order' => '',
        ]));

        self::assertSame([], RecordingProfile::$calls);
    }
}
