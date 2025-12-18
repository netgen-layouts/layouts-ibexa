<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\TestCase;

use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Tests\Validator\ValidatorFactory;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait ValidatorTestCaseTrait
{
    private function createValidator(
        ?Repository $repository = null,
        ?TagsService $tagsService = null,
    ): ValidatorInterface {
        $repository ??= self::createStub(Repository::class);
        $tagsService ??= self::createStub(TagsService::class);

        return Validation::createValidatorBuilder()
            ->setConstraintValidatorFactory(
                new ValidatorFactory($repository, $tagsService),
            )
            ->getValidator();
    }
}
