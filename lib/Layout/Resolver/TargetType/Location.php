<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Layout\Resolver\TargetType;

use Netgen\Layouts\Ibexa\ContentProvider\ContentExtractorInterface;
use Netgen\Layouts\Ibexa\Utils\RemoteIdConverter;
use Netgen\Layouts\Ibexa\Validator\Constraint as IbexaConstraints;
use Netgen\Layouts\Layout\Resolver\TargetType;
use Netgen\Layouts\Layout\Resolver\ValueObjectProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints;

final class Location extends TargetType implements ValueObjectProviderInterface
{
    public function __construct(
        private ContentExtractorInterface $contentExtractor,
        private ValueObjectProviderInterface $valueObjectProvider,
        private RemoteIdConverter $remoteIdConverter,
    ) {}

    public static function getType(): string
    {
        return 'ibexa_location';
    }

    public function getConstraints(): array
    {
        return [
            new Constraints\NotBlank(),
            new Constraints\Type(type: 'int'),
            new Constraints\PositiveOrZero(),
            new IbexaConstraints\Location(allowInvalid: true),
        ];
    }

    public function provideValue(Request $request): ?int
    {
        return $this->contentExtractor->extractLocation($request)?->id;
    }

    public function getValueObject(int|string $value): ?object
    {
        return $this->valueObjectProvider->getValueObject($value);
    }

    public function export(int|string $value): ?string
    {
        return $this->remoteIdConverter->toLocationRemoteId((int) $value);
    }

    public function import(int|string|null $value): int
    {
        return $this->remoteIdConverter->toLocationId((string) $value) ?? 0;
    }
}
