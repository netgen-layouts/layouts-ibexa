<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\HttpCache;

use Ibexa\HttpCache\RepositoryTagPrefix;
use Netgen\Layouts\HttpCache\ClientInterface;
use Netgen\Layouts\Ibexa\HttpCache\RepositoryPrefixDecorator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(RepositoryPrefixDecorator::class)]
final class RepositoryPrefixDecoratorTest extends TestCase
{
    private Stub&ClientInterface $clientStub;

    private Stub&RepositoryTagPrefix $repositoryTagPrefixStub;

    private RepositoryPrefixDecorator $repositoryPrefixDecorator;

    protected function setUp(): void
    {
        $this->clientStub = self::createStub(ClientInterface::class);
        $this->repositoryTagPrefixStub = self::createStub(RepositoryTagPrefix::class);

        $this->repositoryPrefixDecorator = new RepositoryPrefixDecorator(
            $this->clientStub,
            $this->repositoryTagPrefixStub,
        );
    }

    public function testPurge(): void
    {
        $this->repositoryTagPrefixStub
            ->method('getRepositoryPrefix')
            ->willReturn('prefix_');

        $this->clientStub
            ->method('purge')
            ->with(self::identicalTo(['prefix_tag-1', 'prefix_tag-2']));

        $this->repositoryPrefixDecorator->purge(['tag-1', 'tag-2']);
    }

    public function testCommit(): void
    {
        $this->clientStub
            ->method('commit')
            ->willReturn(true);

        self::assertTrue($this->repositoryPrefixDecorator->commit());
    }
}
