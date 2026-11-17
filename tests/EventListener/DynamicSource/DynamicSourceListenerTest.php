<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\EventListener\DynamicSource;

use Contao\ContentModel;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Model;
use Contao\NewsModel;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\EventListener\DynamicSource\DynamicSourceListener;
use Hofff\Contao\ContactProfiles\Model\Profile\ProfileRepository;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Tests\Fixtures\RecordingProfile;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use Hofff\Contao\ContactProfiles\Util\QueryUtil;
use Netzmacht\Contao\Toolkit\Data\Model\RepositoryManager;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

use function serialize;

#[CoversClass(DynamicSourceListener::class)]
#[UsesClass(ListUtil::class)]
#[UsesClass(QueryUtil::class)]
#[UsesClass(ProfileRepository::class)]
#[UsesClass(LoadContactProfilesEvent::class)]
final class DynamicSourceListenerTest extends TestCase
{
    use CreateObjectsTrait;

    public function testFetchesProfilesAsIntegersInStoredOrder(): void
    {
        $this->invokeListener([
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => '',
        ]);

        self::assertCount(1, RecordingProfile::$calls);
        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('FIELD( tl_contact_profile.id,3,1,2)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testFetchesProfilesInConfiguredOrder(): void
    {
        $this->invokeListener([
            'hofff_contact_profiles'       => serialize(['3', '1', '2']),
            'hofff_contact_profiles_order' => serialize(['2', '3', '1']),
        ]);

        self::assertSame([3, 1, 2, '1'], RecordingProfile::$calls[0]['value']);
        self::assertSame('FIELD( tl_contact_profile.id,2,3,1)', RecordingProfile::$calls[0]['options']['order']);
    }

    public function testDoesNotQueryWithoutProfiles(): void
    {
        $this->invokeListener(['hofff_contact_profiles' => '', 'hofff_contact_profiles_order' => '']);

        self::assertSame([], RecordingProfile::$calls);
    }

    /** @param array<string, mixed> $sourceData */
    private function invokeListener(array $sourceData): void
    {
        $sourceModel = $this->createModel(NewsModel::class, $sourceData);
        $listener    = new class (
            $this->createStub(ContaoFramework::class),
            $this->createStub(RepositoryManager::class),
            $this->createProfileRepository(),
            $sourceModel,
        ) extends DynamicSourceListener {
            public function __construct(
                ContaoFramework $framework,
                RepositoryManager $repositoryManager,
                ProfileRepository $repository,
                private Model $sourceModel,
            ) {
                parent::__construct($framework, $repositoryManager, $repository);
            }

            #[Override]
            protected function source(): string
            {
                return 'test';
            }

            #[Override]
            protected function getAlias(): string
            {
                return 'alias';
            }

            #[Override]
            protected function fetchSource(string $alias): Model
            {
                return $this->sourceModel;
            }
        };

        $listener(
            new LoadContactProfilesEvent(
                $this->createModel(ContentModel::class),
                $this->createModel(PageModel::class),
                ['test'],
            ),
        );
    }
}
