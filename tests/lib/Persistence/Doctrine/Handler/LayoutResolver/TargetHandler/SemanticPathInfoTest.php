<?php

namespace Netgen\BlockManager\Ez\Tests\Persistence\Doctrine\Handler\LayoutResolver\TargetHandler;

use Netgen\BlockManager\Ez\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler\SemanticPathInfo;
use Netgen\BlockManager\Tests\Persistence\Doctrine\Handler\LayoutResolver\TargetHandler\AbstractTargetHandlerTest;

class SemanticPathInfoTest extends AbstractTargetHandlerTest
{
    /**
     * @covers \Netgen\BlockManager\Persistence\Doctrine\Handler\LayoutResolverHandler::matchRules
     * @covers \Netgen\BlockManager\Persistence\Doctrine\QueryHandler\LayoutResolverQueryHandler::matchRules
     * @covers \Netgen\BlockManager\Ez\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler\SemanticPathInfo::handleQuery
     */
    public function testMatchRules()
    {
        $rules = $this->handler->matchRules($this->getTargetIdentifier(), '/the/answer');

        $this->assertCount(1, $rules);
        $this->assertEquals(20, $rules[0]->id);
    }

    /**
     * Returns the target identifier under test.
     *
     * @return string
     */
    protected function getTargetIdentifier()
    {
        return 'ez_semantic_path_info';
    }

    /**
     * Creates the handler under test.
     *
     * @return \Netgen\BlockManager\Persistence\Doctrine\QueryHandler\LayoutResolver\TargetHandler
     */
    protected function getTargetHandler()
    {
        return new SemanticPathInfo();
    }
}