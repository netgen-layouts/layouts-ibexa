<?php

declare(strict_types=1);

namespace Netgen\BlockManager\Ez\Tests\Layout\Resolver\TargetType;

use Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validation;

final class SemanticPathInfoTest extends TestCase
{
    /**
     * @var \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo
     */
    private $targetType;

    public function setUp(): void
    {
        $this->targetType = new SemanticPathInfo();
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo::getType
     */
    public function testGetType(): void
    {
        self::assertSame('ez_semantic_path_info', $this->targetType::getType());
    }

    /**
     * @param mixed $value
     * @param bool $isValid
     *
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo::getConstraints
     * @dataProvider validationProvider
     */
    public function testValidation($value, bool $isValid): void
    {
        $validator = Validation::createValidator();

        $errors = $validator->validate($value, $this->targetType->getConstraints());
        self::assertSame($isValid, $errors->count() === 0);
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo::provideValue
     */
    public function testProvideValue(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', '/the/answer');

        self::assertSame(
            '/the/answer',
            $this->targetType->provideValue($request)
        );
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo::provideValue
     */
    public function testProvideValueWithEmptySemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', false);

        self::assertSame(
            '/',
            $this->targetType->provideValue($request)
        );
    }

    /**
     * @covers \Netgen\BlockManager\Ez\Layout\Resolver\TargetType\SemanticPathInfo::provideValue
     */
    public function testProvideValueWithNoSemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');

        self::assertNull($this->targetType->provideValue($request));
    }

    /**
     * Provider for testing target type validation.
     */
    public function validationProvider(): array
    {
        return [
            ['/some/route', true],
            ['/', true],
            ['', false],
            [null, false],
        ];
    }
}
