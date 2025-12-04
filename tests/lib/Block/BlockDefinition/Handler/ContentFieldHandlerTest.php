<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Handler;

use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Netgen\Layouts\API\Values\Block\Block;
use Netgen\Layouts\Block\DynamicParameters;
use Netgen\Layouts\Ibexa\Block\BlockDefinition\Handler\ContentFieldHandler;
use Netgen\Layouts\Ibexa\ContentProvider\ContentProviderInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContentFieldHandler::class)]
final class ContentFieldHandlerTest extends TestCase
{
    private Stub&ContentProviderInterface $contentProviderStub;

    private ContentFieldHandler $handler;

    protected function setUp(): void
    {
        $this->contentProviderStub = self::createStub(ContentProviderInterface::class);

        $this->handler = new ContentFieldHandler($this->contentProviderStub);
    }

    public function testGetDynamicParameters(): void
    {
        $content = new Content();
        $location = new Location();

        $this->contentProviderStub
            ->method('provideContent')
            ->willReturn($content);

        $this->contentProviderStub
            ->method('provideLocation')
            ->willReturn($location);

        $params = new DynamicParameters();

        $this->handler->getDynamicParameters($params, new Block());

        self::assertTrue($params->offsetExists('content'));
        self::assertTrue($params->offsetExists('location'));

        self::assertSame($content, $params['content']);
        self::assertSame($location, $params['location']);
    }
}
