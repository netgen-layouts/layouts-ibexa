<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\HttpCache;

use Netgen\Layouts\Ibexa\HttpCache\LocalClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Toflar\Psr6HttpCacheStore\Psr6StoreInterface;

#[CoversClass(LocalClient::class)]
final class LocalClientTest extends TestCase
{
    private Stub&Psr6StoreInterface $cacheStoreStub;

    private LocalClient $client;

    protected function setUp(): void
    {
        $this->cacheStoreStub = self::createStub(Psr6StoreInterface::class);
        $this->client = new LocalClient($this->cacheStoreStub);
    }

    public function testPurge(): void
    {
        $tags = ['tag-1', 'tag-2'];

        $this->cacheStoreStub
            ->method('invalidateTags')
            ->with(self::identicalTo($tags));

        $this->client->purge($tags);
    }
}
