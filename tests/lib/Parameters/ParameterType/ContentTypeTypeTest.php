<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\ContentTypeService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\ContentType\ContentType as IbexaContentType;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\ContentTypeType;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

use function is_array;

#[CoversClass(ContentTypeType::class)]
final class ContentTypeTypeTest extends TestCase
{
    use ParameterTypeTestTrait;
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ContentTypeService $contentTypeServiceStub;

    protected function setUp(): void
    {
        $this->contentTypeServiceStub = self::createStub(ContentTypeService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getContentTypeService')
            ->willReturn($this->contentTypeServiceStub);

        $this->type = new ContentTypeType();
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('ibexa_content_type', $this->type::getIdentifier());
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
                    'multiple' => false,
                    'types' => [],
                ],
            ],
            [
                [
                    'multiple' => false,
                ],
                [
                    'multiple' => false,
                    'types' => [],
                ],
            ],
            [
                [
                    'multiple' => true,
                ],
                [
                    'multiple' => true,
                    'types' => [],
                ],
            ],
            [
                [
                    'types' => [],
                ],
                [
                    'multiple' => false,
                    'types' => [],
                ],
            ],
            [
                [
                    'types' => ['type1'],
                ],
                [
                    'multiple' => false,
                    'types' => ['type1'],
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
                    'multiple' => 'true',
                ],
            ],
            [
                [
                    'undefined_value' => 'Value',
                ],
            ],
            [
                [
                    'types' => 'type1',
                ],
            ],
        ];
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, bool $required, bool $isValid): void
    {
        $options = [];

        if ($value !== null) {
            $options = ['multiple' => is_array($value)];

            $this->contentTypeServiceStub
                ->method('loadContentTypeByIdentifier')
                ->willReturnCallback(
                    static fn (string $identifier): IbexaContentType => match (true) {
                        $identifier !== 'other' => new IbexaContentType(['identifier' => $identifier]),
                        default => throw new NotFoundException('content type', $identifier),
                    },
                );
        }

        $parameterDefinition = $this->getParameterDefinition($options, $required);
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
            ['news', false, true],
            [[], false, true],
            [['news'], false, true],
            [['article', 'news'], false, true],
            [['article', 'other'], false, false],
            [['other'], false, false],
            [null, false, true],
            ['news', true, true],
            [[], true, false],
            [['news'], true, true],
            [['article', 'news'], true, true],
            [['article', 'other'], true, false],
            [['other'], true, false],
            [null, true, false],
        ];
    }

    #[DataProvider('fromHashDataProvider')]
    public function testFromHash(mixed $value, mixed $convertedValue, bool $multiple): void
    {
        self::assertSame(
            $convertedValue,
            $this->type->fromHash(
                $this->getParameterDefinition(
                    [
                        'multiple' => $multiple,
                    ],
                ),
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
                false,
            ],
            [
                [],
                null,
                false,
            ],
            [
                'type1',
                'type1',
                false,
            ],
            [
                ['type1', 'type2'],
                'type1',
                false,
            ],
            [
                null,
                null,
                true,
            ],
            [
                [],
                null,
                true,
            ],
            [
                'type1',
                ['type1'],
                true,
            ],
            [
                ['type1', 'type2'],
                ['type1', 'type2'],
                true,
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
            [[], true],
            ['type1', false],
            [['type1'], false],
            [0, false],
            [42, false],
            ['', false],
        ];
    }
}
