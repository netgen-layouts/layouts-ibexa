<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper\SemanticPathInfoMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[CoversClass(SemanticPathInfoMapper::class)]
final class SemanticPathInfoMapperTest extends TestCase
{
    private SemanticPathInfoMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SemanticPathInfoMapper();
    }

    public function testGetFormType(): void
    {
        self::assertSame(TextType::class, $this->mapper->getFormType());
    }
}
