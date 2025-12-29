<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Layout\Resolver\TargetType;

use Netgen\Layouts\Ibexa\Layout\Resolver\TargetType\SemanticPathInfoPrefix;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SemanticPathInfoPrefix::class)]
final class SemanticPathInfoPrefixTest extends TestCase
{
    use ValidatorTestCaseTrait;

    private SemanticPathInfoPrefix $targetType;

    protected function setUp(): void
    {
        $this->targetType = new SemanticPathInfoPrefix();
    }

    public function testGetType(): void
    {
        self::assertSame('ibexa_semantic_path_info_prefix', $this->targetType::getType());
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, bool $isValid): void
    {
        $validator = $this->createValidator();

        $errors = $validator->validate($value, $this->targetType->getConstraints());
        self::assertSame($isValid, $errors->count() === 0);
    }

    public function testProvideValue(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', '/the/answer');

        self::assertSame(
            '/the/answer',
            $this->targetType->provideValue($request),
        );
    }

    public function testProvideValueWithEmptySemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');
        $request->attributes->set('semanticPathinfo', false);

        self::assertSame(
            '/',
            $this->targetType->provideValue($request),
        );
    }

    public function testProvideValueWithNoSemanticPathInfo(): void
    {
        $request = Request::create('/the/answer');

        self::assertNull($this->targetType->provideValue($request));
    }

    /**
     * @return iterable<mixed>
     */
    public static function validationDataProvider(): iterable
    {
        return [
            ['/some/route', true],
            ['/', true],
            ['', false],
            [null, false],
        ];
    }
}
