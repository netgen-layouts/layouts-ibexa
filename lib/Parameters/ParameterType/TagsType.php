<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Parameters\ParameterType;

use Ibexa\Contracts\Core\Repository\Exceptions\NotFoundException;
use Netgen\Layouts\Ibexa\Validator\Constraint as IbexaConstraints;
use Netgen\Layouts\Parameters\ParameterDefinition;
use Netgen\Layouts\Parameters\ParameterType;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

use function array_map;
use function is_array;

/**
 * Parameter type used to store and validate an ID of a tag in Netgen Tags.
 */
final class TagsType extends ParameterType
{
    public function __construct(
        private TagsService $tagsService,
    ) {}

    public static function getIdentifier(): string
    {
        return 'netgen_tags';
    }

    public function configureOptions(OptionsResolver $optionsResolver): void
    {
        $optionsResolver
            ->define('min')
            ->required()
            ->default(null)
            ->allowedTypes('int', 'null')
            ->allowedValues(static fn (?int $value): bool => $value === null || $value > 0)
            ->info('It must be a positive integer or null.');

        $optionsResolver
            ->define('max')
            ->required()
            ->default(null)
            ->allowedTypes('int', 'null')
            ->allowedValues(static fn (?int $value): bool => $value === null || $value > 0)
            ->normalize(
                static fn (Options $options, ?int $value): ?int => match (true) {
                    $value === null || $options['min'] === null => $value,
                    $value < $options['min'] => $options['min'],
                    default => $value,
                },
            )->info('It must be a positive integer or null.');

        $optionsResolver
            ->define('allow_invalid')
            ->required()
            ->default(false)
            ->allowedTypes('bool');
    }

    public function fromHash(ParameterDefinition $parameterDefinition, mixed $value): mixed
    {
        return is_array($value) ? array_map('intval', $value) : $value;
    }

    public function export(ParameterDefinition $parameterDefinition, mixed $value): ?string
    {
        try {
            /** @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag */
            $tag = $this->tagsService->sudo(
                fn (): Tag => $this->tagsService->loadTag((int) $value),
            );

            return $tag->remoteId;
        } catch (NotFoundException) {
            return null;
        }
    }

    public function import(ParameterDefinition $parameterDefinition, mixed $value): ?int
    {
        try {
            /** @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag */
            $tag = $this->tagsService->sudo(
                fn (): Tag => $this->tagsService->loadTagByRemoteId((string) $value),
            );

            return $tag->id;
        } catch (NotFoundException) {
            return null;
        }
    }

    protected function getValueConstraints(ParameterDefinition $parameterDefinition, mixed $value): array
    {
        $options = $parameterDefinition->options;

        $constraints = [
            new Constraints\Type(type: 'array'),
            new Constraints\All(
                constraints: [
                    new Constraints\NotBlank(),
                    new Constraints\Type(type: 'numeric'),
                    new Constraints\Positive(),
                    new IbexaConstraints\Tag(allowInvalid: $options['allow_invalid']),
                ],
            ),
        ];

        if ($options['min'] !== null || $options['max'] !== null) {
            $constraints[] = new Constraints\Count(
                min: $options['min'],
                max: $options['max'],
            );
        }

        return $constraints;
    }
}
