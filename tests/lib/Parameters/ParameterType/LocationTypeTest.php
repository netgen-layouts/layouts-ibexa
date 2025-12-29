<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\LocationService;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\Content\Content;
use Ibexa\Core\Repository\Values\Content\Location;
use Ibexa\Core\Repository\Values\ContentType\ContentType;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\LocationType;
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

#[CoversClass(LocationType::class)]
final class LocationTypeTest extends TestCase
{
    use ParameterTypeTestTrait;
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ValueObjectProviderInterface $valueObjectProviderStub;

    private Stub&LocationService $locationServiceStub;

    protected function setUp(): void
    {
        $this->locationServiceStub = self::createStub(LocationService::class);

        $this->repositoryStub = self::createStub(Repository::class);
        $this->valueObjectProviderStub = self::createStub(ValueObjectProviderInterface::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getLocationService')
            ->willReturn($this->locationServiceStub);

        $this->type = new LocationType($this->repositoryStub, $this->valueObjectProviderStub);
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('ibexa_location', $this->type::getIdentifier());
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
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willReturn(new Location(['remoteId' => 'abc']));

        self::assertSame('abc', $this->type->export($this->getParameterDefinition(), 42));
    }

    public function testExportWithNonExistingLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocation')
            ->with(self::identicalTo(42))
            ->willThrowException(new NotFoundException('location', 42));

        self::assertNull($this->type->export($this->getParameterDefinition(), 42));
    }

    public function testImport(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willReturn(new Location(['id' => 42]));

        self::assertSame(42, $this->type->import($this->getParameterDefinition(), 'abc'));
    }

    public function testImportWithNonExistingLocation(): void
    {
        $this->locationServiceStub
            ->method('loadLocationByRemoteId')
            ->with(self::identicalTo('abc'))
            ->willThrowException(new NotFoundException('location', 'abc'));

        self::assertNull($this->type->import($this->getParameterDefinition(), 'abc'));
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, string $type, bool $required, bool $isValid): void
    {
        if ($value !== null) {
            $this->locationServiceStub
                ->method('loadLocation')
                ->with(self::identicalTo((int) $value))
                ->willReturnCallback(
                    static fn (): Location => match (true) {
                        is_int($value) && $value > 0 => new Location(
                            [
                                'id' => $value,
                                'content' => new Content(
                                    [
                                        'contentType' => new ContentType(['identifier' => $type]),
                                    ],
                                ),
                            ],
                        ),
                        default => throw new NotFoundException('location', $value),
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
            [12, 'user', false, true],
            [12, 'image', false, true],
            [12, 'article', false, false],
            [-12, 'user', false, false],
            [0, 'user', false, false],
            [null, 'user', false, true],
            [12, 'user', true, true],
            [12, 'image', true, true],
            [12, 'article', true, false],
            [-12, 'user', true, false],
            [0, 'user', true, false],
            [null, 'user', true, false],
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
            [new Location(), false],
        ];
    }

    public function testGetValueObject(): void
    {
        $location = new Location();

        $this->valueObjectProviderStub
            ->method('getValueObject')
            ->with(self::identicalTo(42))
            ->willReturn($location);

        /** @var \Netgen\Layouts\Ibexa\Parameters\ParameterType\LocationType $type */
        $type = $this->type;

        self::assertSame($location, $type->getValueObject(42));
    }
}
