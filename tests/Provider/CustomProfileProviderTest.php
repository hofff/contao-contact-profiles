<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Provider;

use Contao\ContentModel;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Provider\AbstractProfileProvider;
use Hofff\Contao\ContactProfiles\Provider\CustomProfileProvider;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Tests\Fixtures\RecordingProfile;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function serialize;

#[CoversClass(CustomProfileProvider::class)]
#[UsesClass(AbstractProfileProvider::class)]
#[UsesClass(ListUtil::class)]
#[UsesClass(QueryUtil::class)]
#[UsesClass(ProfileRepository::class)]
final class CustomProfileProviderTest extends TestCase
{
    use CreateObjectsTrait;

    public function testFetchProfilesPassesProfileIdsAsIntegersInStoredOrder(): void
    {
        $provider = new CustomProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, [
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => '',
        ]);

        self::assertSame([], $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0));
        self::assertCount(1, RecordingProfile::$calls);
        self::assertSame('findBy', RecordingProfile::$calls[0]['method']);
        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame(
            ['tl_contact_profile.id IN(?,?,?)', 'tl_contact_profile.published=?'],
            RecordingProfile::$calls[0]['column'],
        );
        self::assertSame('FIELD( tl_contact_profile.id,3,1,2)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testFetchProfilesUsesConfiguredOrder(): void
    {
        $provider = new CustomProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, [
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => serialize(['2', '3', '1']),
        ]);

        $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0);

        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('FIELD( tl_contact_profile.id,2,3,1)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testFetchProfilesKeepsSqlOrderOption(): void
    {
        $provider = new CustomProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, [
            'hofff_contact_profiles'           => serialize(['3', '1']),
            'hofff_contact_profiles_order_sql' => '.lastname',
        ]);

        $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0);

        self::assertSame([3, 1, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('tl_contact_profile.lastname', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testFetchProfilesWithoutProfilesDoesNotQuery(): void
    {
        $provider = new CustomProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, ['hofff_contact_profiles' => '']);

        self::assertSame([], $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0));
        self::assertSame([], RecordingProfile::$calls);
    }

    public function testCountTotalPassesProfileIdsAsIntegers(): void
    {
        $provider = new CustomProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, [
            'hofff_contact_profiles' => serialize(['3', '1', '2']),
        ]);

        RecordingProfile::$count = 3;

        self::assertSame(3, $provider->countTotal($model, []));
        self::assertSame('countBy', RecordingProfile::$calls[0]['method']);
        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
    }
}
