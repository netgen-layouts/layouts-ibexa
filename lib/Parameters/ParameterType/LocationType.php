<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Ibexa\Contracts\Core\Repository\Repository;
use Ibexa\Contracts\Core\Repository\Values\Content\Location;
use Netgen\Layouts\Ibexa\Validator\Constraint as IbexaConstraints;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\Layouts\Parameters\ValueObjectProviderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

/**
 * Parameter type used to store and validate an ID of a location in Ibexa CMS.
 */
final class LocationType extends ParameterType implements ValueObjectProviderInterface
{
    public function __construct(
        private Repository $repository,
        private ValueObjectProviderInterface $valueObjectProvider,
    ) {}

    public static function getIdentifier(): string
    {
        return 'ibexa_location';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->define('allow_invalid')
            ->required()
            ->default(false)
            ->allowedTypes('bool');

        $optionsResolver
            ->define('allowed_types')
            ->required()
            ->default([])
            ->allowedTypes('string[]');
    }

    public function fromHash(ParameterDefinition $parameterDefinition, mixed $value): ?int
    {
        return $value !== null ? (int) $value : null;
    }

    public function export(ParameterDefinition $parameterDefinition, mixed $value): ?string
    {
        try {
            return $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocation((int) $value),
            )->remoteId;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function import(ParameterDefinition $parameterDefinition, mixed $value): ?int
    {
        try {
            return $this->repository->sudo(
                static fn (Repository $repository): Location => $repository->getLocationService()->loadLocationByRemoteId((string) $value),
            )->id;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function getValueObject(mixed $value): ?object
    {
        return $this->valueObjectProvider->getValueObject($value);
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, mixed $value): array
    {
        return [
            new Constraints\Type(type: 'numeric'),
            new Constraints\Positive(),
            new IbexaConstraints\Location(
                allowedTypes: $parameterDefinition->getOption('allowed_types'),
                allowInvalid: $parameterDefinition->getOption('allow_invalid'),
            ),
        ];
    }
}
