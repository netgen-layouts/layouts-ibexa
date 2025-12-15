<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\Form\TargetType\Mapper;

use Netgen\Layouts\Ibexa\Layout\Resolver\Form\TargetType\Mapper\SemanticPathInfoPrefixMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Extension\Core\Type\TextType;

#[CoversClass(SemanticPathInfoPrefixMapper::class)]
final class SemanticPathInfoPrefixMapperTest extends TestCase
{
    private SemanticPathInfoPrefixMapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new SemanticPathInfoPrefixMapper();
    }

    public function testGetFormType(): void
    {
        self::assertSame(TextType::class, $this->mapper->getFormType());
    }
}
