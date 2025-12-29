<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\SectionService;
use Ibexa\Contracts\Core\Repository\Values\Content\Section as IbexaSection;
use Ibexa\Core\Base\Exceptions\NotFoundException;
use Ibexa\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\SectionType;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

use function is_array;

#[CoversClass(SectionType::class)]
final class SectionTypeTest extends TestCase
{
    use ParameterTypeTestTrait;
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&SectionService $sectionServiceStub;

    protected function setUp(): void
    {
        $this->sectionServiceStub = self::createStub(SectionService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getSectionService')
            ->willReturn($this->sectionServiceStub);

        $this->type = new SectionType();
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('ibexa_section', $this->type::getIdentifier());
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
                    'sections' => [],
                ],
            ],
            [
                [
                    'multiple' => false,
                ],
                [
                    'multiple' => false,
                    'sections' => [],
                ],
            ],
            [
                [
                    'multiple' => true,
                ],
                [
                    'multiple' => true,
                    'sections' => [],
                ],
            ],
            [
                [
                    'sections' => [],
                ],
                [
                    'multiple' => false,
                    'sections' => [],
                ],
            ],
            [
                [
                    'sections' => ['media'],
                ],
                [
                    'multiple' => false,
                    'sections' => ['media'],
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
                    'sections' => 'section1',
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

            $this->sectionServiceStub
                ->method('loadSectionByIdentifier')
                ->willReturnCallback(
                    static fn (string $identifier): IbexaSection => match (true) {
                        $identifier !== 'other' => new IbexaSection(['identifier' => $identifier]),
                        default => throw new NotFoundException('section', $identifier),
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
            ['standard', false, true],
            [[], false, true],
            [['standard'], false, true],
            [['media', 'standard'], false, true],
            [['media', 'other'], false, false],
            [['other'], false, false],
            [null, false, true],
            ['standard', true, true],
            [[], true, false],
            [['standard'], true, true],
            [['media', 'standard'], true, true],
            [['media', 'other'], true, false],
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
                'section1',
                'section1',
                false,
            ],
            [
                ['section1', 'section2'],
                'section1',
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
                'section1',
                ['section1'],
                true,
            ],
            [
                ['section1', 'section2'],
                ['section1', 'section2'],
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
            ['section1', false],
            [['section1'], false],
            [0, false],
            ['42', false],
            ['', false],
        ];
    }
}
