<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Block\BlockDefinition\Integration;

use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\Layouts\API\Service\LayoutResolverService;
use Netgen\Layouts\API\Service\LayoutService;
use Netgen\Layouts\Block\BlockDefinition\BlockDefinitionHandlerInterface;
use Netgen\Layouts\Ibexa\Block\BlockDefinition\Handler\ComponentHandler;
use Netgen\Layouts\Ibexa\Parameters\ParameterType as IbexaParameterType;
use Netgen\Layouts\Ibexa\Tests\Validator\ValidatorFactory;
use Netgen\Layouts\Item\CmsItemLoaderInterface;
use Netgen\Layouts\Parameters\ValueObjectProviderInterface;
use Netgen\Layouts\Tests\Block\BlockDefinition\Integration\BlockTestCase;
use Netgen\Layouts\Tests\TestCase\ValidatorFactory as BaseValidatorFactory;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class ComponentTestBase extends BlockTestCase
{
    final protected function setUp(): void
    {
        parent::setUp();

        $repositoryStub = self::createStub(Repository::class);
        $valueObjectProviderStub = self::createStub(ValueObjectProviderInterface::class);

        (function () use ($repositoryStub, $valueObjectProviderStub): void {
            $contentParameterType = new IbexaParameterType\ContentType(
                $repositoryStub,
                $valueObjectProviderStub,
            );

            $this->parameterTypes[$contentParameterType::getIdentifier()] = $contentParameterType;
            $this->parameterTypesByClass[IbexaParameterType\ContentType::class] = $contentParameterType;
        })->call($this->parameterTypeRegistry);
    }

    final public static function parametersDataProvider(): iterable
    {
        return [
            [
                [
                    'content_type_identifier' => 'foo',
                ],
                [
                    'content_type_identifier' => 'foo',
                    'content' => null,
                ],
            ],
            [
                [
                    'content_type_identifier' => 'foo',
                    'content' => null,
                ],
                [
                    'content_type_identifier' => 'foo',
                    'content' => null,
                ],
            ],
            [
                [
                    'content_type_identifier' => 'foo',
                    'content' => 42,
                ],
                [
                    'content_type_identifier' => 'foo',
                    'content' => 42,
                ],
            ],
            [
                [
                    'unknown' => 'unknown',
                ],
                [],
            ],
        ];
    }

    final public static function invalidParametersDataProvider(): iterable
    {
        return [
            [
                [],
            ],
            [
                [
                    'content_type_identifier' => null,
                    'content' => null,
                ],
            ],
            [
                [
                    'content_type_identifier' => '',
                    'content' => null,
                ],
            ],
            [
                [
                    'content_type_identifier' => 42,
                    'content' => null,
                ],
            ],
            [
                [
                    'content_type_identifier' => null,
                    'content' => '42',
                ],
            ],
        ];
    }

    final protected function createValidator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(
                new ValidatorFactory(
                    new BaseValidatorFactory(
                        self::createStub(LayoutService::class),
                        self::createStub(LayoutResolverService::class),
                        self::createStub(CmsItemLoaderInterface::class),
                    ),
                    self::createStub(Repository::class),
                ),
            )
            ->getValidator();
    }

    final protected function createBlockDefinitionHandler(): BlockDefinitionHandlerInterface
    {
        return new ComponentHandler();
    }
}
