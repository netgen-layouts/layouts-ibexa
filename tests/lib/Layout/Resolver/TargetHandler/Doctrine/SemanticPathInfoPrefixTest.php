<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetHandler\Doctrine;

use Netgen\Layouts\Ibexa\Layout\Resolver\TargetHandler\Doctrine\SemanticPathInfoPrefix;
use Netgen\Layouts\Persistence\Doctrine\QueryHandler\TargetHandlerInterface;
use Netgen\Layouts\Persistence\Values\LayoutResolver\RuleGroup;
use Netgen\Layouts\Persistence\Values\Status;
use Netgen\Layouts\Tests\Layout\Resolver\TargetHandler\Doctrine\TargetHandlerTestBase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SemanticPathInfoPrefix::class)]
final class SemanticPathInfoPrefixTest extends TargetHandlerTestBase
{
    public function testMatchRules(): void
    {
        $rules = $this->handler->matchRules(
            $this->handler->loadRuleGroup(RuleGroup::ROOT_UUID, Status::Published),
            $this->getTargetIdentifier(),
            '/the/',
        );

        self::assertCount(1, $rules);
        self::assertSame(11, $rules[0]->id);
    }

    protected function getTargetIdentifier(): string
    {
        return 'ibexa_semantic_path_info_prefix';
    }

    protected function getTargetHandler(): TargetHandlerInterface
    {
        return new SemanticPathInfoPrefix($this->databaseConnection);
    }

    protected function provideFixturesPath(): string
    {
        return __DIR__ . '/../../../../../_fixtures';
    }
}
