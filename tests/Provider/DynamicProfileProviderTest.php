<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Provider;

use Contao\ContentModel;
use Contao\PageModel;
use Hofff\Contao\ContactProfiles\Event\LoadContactProfilesEvent;
use Hofff\Contao\ContactProfiles\Provider\AbstractProfileProvider;
use Hofff\Contao\ContactProfiles\Provider\DynamicProfileProvider;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function serialize;

#[CoversClass(DynamicProfileProvider::class)]
#[UsesClass(AbstractProfileProvider::class)]
#[UsesClass(ListUtil::class)]
#[UsesClass(LoadContactProfilesEvent::class)]
final class DynamicProfileProviderTest extends TestCase
{
    use CreateObjectsTrait;

    /** @return iterable<string, array{mixed, list<string>}> */
    public static function sourcesProvider(): iterable
    {
        yield 'no sources' => ['', []];
        yield 'single source' => [serialize(['news']), ['news']];
        yield 'multiple sources' => [serialize(['news', 'faq', 'events']), ['news', 'faq', 'events']];
        yield 'values are only cast, not filtered' => [serialize(['news', '', 0]), ['news', '', '0']];
    }

    /** @param list<string> $expected */
    #[DataProvider('sourcesProvider')]
    public function testFetchProfilesDispatchesConfiguredSources(mixed $sources, array $expected): void
    {
        $events   = [];
        $provider = new DynamicProfileProvider($this->createRecordingDispatcher($events));
        $model    = $this->createModel(ContentModel::class, ['hofff_contact_sources' => $sources]);

        self::assertSame([], $provider->fetchProfiles($model, $this->createModel(PageModel::class), null, 0));
        self::assertCount(1, $events);
        self::assertSame($expected, $events[0]->sources());
    }

    /** @param list<string> $expected */
    #[DataProvider('sourcesProvider')]
    public function testCalculateInitialsDispatchesConfiguredSources(mixed $sources, array $expected): void
    {
        $events   = [];
        $provider = new DynamicProfileProvider($this->createRecordingDispatcher($events));
        $model    = $this->createModel(ContentModel::class, ['hofff_contact_sources' => $sources]);

        $provider->calculateInitials($model, $this->createModel(PageModel::class));

        self::assertCount(1, $events);
        self::assertSame($expected, $events[0]->sources());
    }

    /** @param list<LoadContactProfilesEvent> $events */
    private function createRecordingDispatcher(array &$events): EventDispatcherInterface
    {
        $dispatcher = $this->createStub(EventDispatcherInterface::class);
        $dispatcher
            ->method('dispatch')
            ->willReturnCallback(static function (object $event) use (&$events): object {
                if ($event instanceof LoadContactProfilesEvent) {
                    $events[] = $event;
                }

                return $event;
            });

        return $dispatcher;
    }
}
