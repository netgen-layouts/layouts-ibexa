<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\HttpCache;

use Netgen\Layouts\Ibexa\HttpCache\LocalClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Toflar\Psr6HttpCacheStore\Psr6StoreInterface;

#[CoversClass(LocalClient::class)]
final class LocalClientTest extends TestCase
{
    private MockObject&Psr6StoreInterface $cacheStoreMock;

    private LocalClient $client;

    protected function setUp(): void
    {
        $this->cacheStoreMock = $this->createMock(Psr6StoreInterface::class);
        $this->client = new LocalClient($this->cacheStoreMock);
    }

    public function testPurge(): void
    {
        $tags = ['tag-1', 'tag-2'];

        $this->cacheStoreMock
            ->expects(self::once())
            ->method('invalidateTags')
            ->with(self::identicalTo($tags));

        $this->client->purge($tags);
    }

    public function testCommit(): void
    {
        self::assertTrue($this->client->commit());
    }
}
