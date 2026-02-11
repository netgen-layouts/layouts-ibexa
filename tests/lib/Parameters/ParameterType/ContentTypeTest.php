<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\ContentService;
use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\ContentInfo;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\ContentType\ContentType as IbexaContentType;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\ContentType;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ValueObjectProviderInterface;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

use function is_int;

#[CoversClass(ContentType::class)]
final class ContentTypeTest extends TestCase
{
    use ParameterTypeTestTrait;
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ValueObjectProviderInterface $valueObjectProviderStub;

    private Stub&ContentService $contentServiceStub;

    protected function setUp(): void
    {
        $this->contentServiceStub = self::createStub(ContentService::class);

        $contentTypeServiceStub = self::createStub(ContentTypeService::class);
        $contentTypeServiceStub
            ->method('loadContentType')
            ->willReturnCallback(
                static fn (int $type): IbexaContentType => match ($type) {
                    24 => new IbexaContentType(['identifier' => 'user']),
                    42 => new IbexaContentType(['identifier' => 'image']),
                    default => new IbexaContentType(['identifier' => 'article']),
                },
            );

        $this->repositoryStub = self::createStub(Repository::class);
        $this->valueObjectProviderStub = self::createStub(ValueObjectProviderInterface::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getContentService')
            ->willReturn($this->contentServiceStub);

        $this->repositoryStub
            ->method('getContentTypeService')
            ->willReturn($contentTypeServiceStub);

        $this->type = new ContentType($this->repositoryStub, $this->valueObjectProviderStub);
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('ibexa_content', $this->type::getIdentifier());
    }

    /**
     * @param array<string, mixed> $options
     * @param array<string, mixed> $resolvedOptions
     */
    #[DataProvider('validOptionsDataProvider')]
    public function testValidOptions(array $options, array $resolvedOptions): void
    {
        $parameterDefinition = $this->getParameterDefinition($options);
        self::assertSame($resolvedOptions, $parameterDefinition->options);
    }

    /**
     * @param array<string, mixed> $options
     */
    #[DataProvider('invalidOptionsDataProvider')]
    public function testInvalidOptions(array $options): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->getParameterDefinition($options);
    }

    /**
     * @return iterable<mixed>
     */
    public static function validOptionsDataProvider(): iterable
    {
        return [
            [
                [],
                [
                    'allow_invalid' => false,
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allow_invalid' => false,
                ],
                [
                    'allow_invalid' => false,
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allow_invalid' => true,
                ],
                [
                    'allow_invalid' => true,
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allowed_types' => [],
                ],
                [
                    'allow_invalid' => false,
                    'allowed_types' => [],
                ],
            ],
            [
                [
                    'allowed_types' => ['image', 'user'],
                ],
                [
                    'allow_invalid' => false,
                    'allowed_types' => ['image', 'user'],
                ],
            ],
        ];
    }

    /**
     * @return iterable<mixed>
     */
    public static function invalidOptionsDataProvider(): iterable
    {
        return [
            [
                [
                    'allow_invalid' => 'false',
                ],
            ],
            [
                [
                    'allow_invalid' => 'true',
                ],
            ],
            [
                [
                    'allow_invalid' => 0,
                ],
            ],
            [
                [
                    'allow_invalid' => 1,
                ],
            ],
            [
                [
                    'allowed_types' => 'image',
                ],
            ],
            [
                [
                    'allowed_types' => [42],
                ],
            ],
            [
                [
                    'undefined_value' => 'Value',
                ],
            ],
        ];
    }

    public function testExport(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willReturn(new ContentInfo(['remoteId' => 'abc']));

        self::assertSame('abc', $this->type->export($this->getParameterDefinition(), 42));
    }

    public function testExportWithNonExistingContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfo')
            ->willThrowException(new NotFoundException('contentInfo', 42));

        self::assertNull($this->type->export($this->getParameterDefinition(), 42));
    }

    public function testImport(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willReturn(new ContentInfo(['id' => 42]));

        self::assertSame(42, $this->type->import($this->getParameterDefinition(), 'abc'));
    }

    public function testImportWithNonExistingContent(): void
    {
        $this->contentServiceStub
            ->method('loadContentInfoByRemoteId')
            ->willThrowException(new NotFoundException('contentInfo', 'abc'));

        self::assertNull($this->type->import($this->getParameterDefinition(), 'abc'));
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, int $type, bool $required, bool $isValid): void
    {
        if ($value !== null) {
            $this->contentServiceStub
                ->method('loadContentInfo')
                    ->willReturnCallback(
                        static fn (): ContentInfo => match (true) {
                            is_int($value) && $value > 0 => new ContentInfo(['id' => $value, 'contentTypeId' => $type]),
                            default => throw new NotFoundException('content', $value),
                        },
                    );
        }

        $parameterDefinition = $this->getParameterDefinition(['allowed_types' => ['user', 'image']], $required);
        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate($value, $this->type->getConstraints($parameterDefinition, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    /**
     * @return iterable<mixed>
     */
    public static function validationDataProvider(): iterable
    {
        return [
            [12, 24, false, true],
            [12, 42, false, true],
            [12, 52, false, false],
            [-12, 24, false, false],
            [0, 24, false, false],
            [null, 24, false, true],
            [12, 24, true, true],
            [12, 42, true, true],
            [12, 52, true, false],
            [-12, 24, true, false],
            [0, 24, true, false],
            [null, 24, true, false],
        ];
    }

    #[DataProvider('fromHashDataProvider')]
    public function testFromHash(mixed $value, mixed $convertedValue): void
    {
        self::assertSame(
            $convertedValue,
            $this->type->fromHash(
                $this->getParameterDefinition(),
                $value,
            ),
        );
    }

    /**
     * @return iterable<mixed>
     */
    public static function fromHashDataProvider(): iterable
    {
        return [
            [
                null,
                null,
            ],
            [
                '42',
                42,
            ],
            [
                42,
                42,
            ],
        ];
    }

    #[DataProvider('emptyDataProvider')]
    public function testIsValueEmpty(mixed $value, bool $isEmpty): void
    {
        self::assertSame($isEmpty, $this->type->isValueEmpty(new ParameterDefinition(), $value));
    }

    /**
     * @return iterable<mixed>
     */
    public static function emptyDataProvider(): iterable
    {
        return [
            [null, true],
            [new ContentInfo(), false],
        ];
    }

    public function testGetValueObject(): void
    {
        $content = new Content();

        $this->valueObjectProviderStub
            ->method('getValueObject')
            ->willReturn($content);

        /** @var \Netgen\Layouts\Ibexa\Parameters\ParameterType\ContentType $type */
        $type = $this->type;

        self::assertSame($content, $type->getValueObject(42));
    }
}
