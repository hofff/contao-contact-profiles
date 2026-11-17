<?php

declare(strict_types=1);

namespace Hofff\Contao\ContactProfiles\Tests\Renderer;

use Contao\ContentModel;
use Hofff\Contao\Consent\Bridge\ConsentId\ConsentIdParser;
use Hofff\Contao\ContactProfiles\Renderer\ContactProfileRendererFactory;
use Hofff\Contao\ContactProfiles\Renderer\FieldRenderer;
use Hofff\Contao\ContactProfiles\Routing\ContactProfileUrlGenerator;
use Hofff\Contao\ContactProfiles\Tests\CreateObjectsTrait;
use Hofff\Contao\ContactProfiles\Util\ListUtil;
use Netzmacht\Contao\Toolkit\Routing\RequestScopeMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Symfony\Contracts\Translation\TranslatorInterface;

use function serialize;

#[CoversClass(ContactProfileRendererFactory::class)]
#[UsesClass(ListUtil::class)]
final class ContactProfileRendererFactoryTest extends TestCase
{
    use CreateObjectsTrait;

    /** @return iterable<string, array{mixed, list<string>}> */
    public static function imageSizeProvider(): iterable
    {
        // Regression: the image size id was moved to the width position (['', '', '55'] => ['55']).
        yield 'image size id' => [serialize(['', '', '55']), ['', '', '55']];
        yield 'custom dimensions' => [serialize(['180', '180', 'crop']), ['180', '180', 'crop']];
        yield 'width only' => [serialize(['200', '', 'proportional']), ['200', '', 'proportional']];
        yield 'integer values' => [serialize([180, 180, 'crop']), ['180', '180', 'crop']];
        yield 'nothing configured' => [serialize(['', '', '']), ['', '', '']];
    }

    /** @param list<string> $expected */
    #[DataProvider('imageSizeProvider')]
    public function testImageSizeKeepsPositions(mixed $size, array $expected): void
    {
        $renderer = $this->createFactory()->create(
            $this->createModel(ContentModel::class, ['size' => $size, 'hofff_contact_more' => 'More']),
        );

        self::assertSame($expected, $renderer->imageSize());
    }

    public function testNoImageSizeIsSetWithoutConfiguration(): void
    {
        $renderer = $this->createFactory()->create(
            $this->createModel(ContentModel::class, ['size' => '', 'hofff_contact_more' => 'More']),
        );

        self::assertNull($renderer->imageSize());
    }

    private function createFactory(): ContactProfileRendererFactory
    {
        $scopeMatcher = $this->createStub(RequestScopeMatcher::class);
        $scopeMatcher->method('isFrontendRequest')->willReturn(false);

        return new ContactProfileRendererFactory(
            $this->createStub(TranslatorInterface::class),
            $this->createStub(ConsentIdParser::class),
            $this->createStub(FieldRenderer::class),
            (new ReflectionClass(ContactProfileUrlGenerator::class))->newInstanceWithoutConstructor(),
            $scopeMatcher,
        );
    }
}
