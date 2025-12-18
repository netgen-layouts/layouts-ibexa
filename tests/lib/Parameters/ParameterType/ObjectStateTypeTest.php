<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\ObjectStateService;
use Ibexa\Core\Repository\Repository;
use Ibexa\Core\Repository\Values\ObjectState\ObjectState as IbexaObjectState;
use Ibexa\Core\Repository\Values\ObjectState\ObjectStateGroup;
use Netgen\Layouts\Ibexa\Parameters\ParameterType\ObjectStateType;
use Netgen\Layouts\Ibexa\Tests\TestCase\ValidatorTestCaseTrait;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Tests\Parameters\ParameterType\ParameterTypeTestTrait;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Symfony\Component\OptionsResolver\Exception\InvalidArgumentException;

use function is_array;

#[CoversClass(ObjectStateType::class)]
final class ObjectStateTypeTest extends TestCase
{
    use ParameterTypeTestTrait;
    use ValidatorTestCaseTrait;

    private Stub&Repository $repositoryStub;

    private Stub&ObjectStateService $objectStateServiceStub;

    protected function setUp(): void
    {
        $this->objectStateServiceStub = self::createStub(ObjectStateService::class);
        $this->repositoryStub = self::createStub(Repository::class);

        $this->repositoryStub
            ->method('sudo')
            ->with(self::anything())
            ->willReturnCallback(
                fn (callable $callback): mixed => $callback($this->repositoryStub),
            );

        $this->repositoryStub
            ->method('getObjectStateService')
            ->willReturn($this->objectStateServiceStub);

        $this->type = new ObjectStateType();
    }

    public function testGetIdentifier(): void
    {
        self::assertSame('ibexa_object_state', $this->type::getIdentifier());
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
     * Provider for testing valid parameter attributes.
     */
    public static function validOptionsDataProvider(): iterable
    {
        return [
            [
                [],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'multiple' => false,
                ],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'multiple' => true,
                ],
                [
                    'multiple' => true,
                    'states' => [],
                ],
            ],
            [
                [
                    'states' => [],
                ],
                [
                    'multiple' => false,
                    'states' => [],
                ],
            ],
            [
                [
                    'states' => ['state1'],
                ],
                [
                    'multiple' => false,
                    'states' => ['state1'],
                ],
            ],
        ];
    }

    /**
     * Provider for testing invalid parameter attributes.
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
                    'states' => 'state1',
                ],
            ],
        ];
    }

    #[DataProvider('validationDataProvider')]
    public function testValidation(mixed $value, bool $required, bool $isValid): void
    {
        $group1 = new ObjectStateGroup(['identifier' => 'group1']);
        $group2 = new ObjectStateGroup(['identifier' => 'group2']);

        $this->objectStateServiceStub
            ->method('loadObjectStateGroups')
            ->willReturn([$group1, $group2]);

        $this->objectStateServiceStub
            ->method('loadObjectStates')
            ->willReturnMap(
                [
                    [
                        $group1,
                        [],
                        [
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state1',
                                ],
                            ),
                            new IbexaObjectState(
                                [
                                    'identifier' => 'state2',
                                ],
                            ),
                        ],
                    ],
                    [
                        $group2,
                        [],
                        [],
                    ],
                ],
            );

        $options = $value !== null ? ['multiple' => is_array($value)] : [];
        $parameterDefinition = $this->getParameterDefinition($options, $required);
        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate($value, $this->type->getConstraints($parameterDefinition, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    #[DataProvider('validationWithEmptyValuesDataProvider')]
    public function testValidationWithEmptyValues(mixed $value, bool $required, bool $isValid): void
    {
        $options = $value !== null ? ['multiple' => is_array($value)] : [];
        $parameterDefinition = $this->getParameterDefinition($options, $required);
        $validator = $this->createValidator($this->repositoryStub);

        $errors = $validator->validate($value, $this->type->getConstraints($parameterDefinition, $value));
        self::assertSame($isValid, $errors->count() === 0);
    }

    public static function validationDataProvider(): iterable
    {
        return [
            ['group1|state2', false, true],
            [['group1|state2'], false, true],
            [['group1|state1', 'group1|state2'], false, true],
            [['group1|state1', 'group2|state1'], false, false],
            [['group2|state1'], false, false],
            [['unknown|state1'], false, false],
            [['group1|unknown'], false, false],
            ['group1|state2', true, true],
            [['group1|state2'], true, true],
            [['group1|state1', 'group1|state2'], true, true],
            [['group1|state1', 'group2|state1'], true, false],
            [['group2|state1'], true, false],
            [['unknown|state1'], true, false],
            [['group1|unknown'], true, false],
        ];
    }

    public static function validationWithEmptyValuesDataProvider(): iterable
    {
        return [
            [[], false, true],
            [null, false, true],
            [[], true, false],
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
                'state1',
                'state1',
                false,
            ],
            [
                ['state1', 'state2'],
                'state1',
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
                'state1',
                ['state1'],
                true,
            ],
            [
                ['state1', 'state2'],
                ['state1', 'state2'],
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
     * Provider for testing if the value is empty.
     */
    public static function emptyDataProvider(): iterable
    {
        return [
            [null, true],
            [[], true],
            ['state1', false],
            [['state1'], false],
            [0, false],
            ['42', false],
            ['', false],
        ];
    }
}
