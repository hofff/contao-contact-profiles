<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Provider;

use Contao\ContentModel;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Provider\AbstractProfileProvider;
use Hofff\Contao\ContactProfiles\Provider\CategoriesProfileProvider;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Tests\Fixtures\RecordingProfile;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function serialize;

#[CoversClass(CategoriesProfileProvider::class)]
#[UsesClass(AbstractProfileProvider::class)]
#[UsesClass(ListUtil::class)]
#[UsesClass(ProfileRepository::class)]
final class CategoriesProfileProviderTest extends TestCase
{
    use CreateObjectsTrait;

    public function testFetchProfilesPassesCategoryIdsAsIntegers(): void
    {
        $provider = new CategoriesProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, [
            'hofff_contact_categories' => serialize(['5', '7']),
        ]);

        self::assertSame([], $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0));
        self::assertCount(1, RecordingProfile::$calls);
        self::assertSame('findBy', RecordingProfile::$calls[0]['method']);
        self::assertSame([5, 7, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame(
            ['tl_contact_profile.pid IN(?,?)', 'tl_contact_profile.published=?'],
            RecordingProfile::$calls[0]['column'],
        );
    }

    public function testFetchProfilesWithoutCategoriesDoesNotQuery(): void
    {
        $provider = new CategoriesProfileProvider($this->createProfileRepository());
        $model    = $this->createModel(ContentModel::class, ['hofff_contact_categories' => '']);

        self::assertSame([], $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0));
        self::assertSame([], RecordingProfile::$calls);
    }
}
