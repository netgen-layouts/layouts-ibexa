<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Validator\ContentValidator;
use Netgen\Layouts\Ibexa\Validator\SiteAccessGroupValidator;
use Netgen\Layouts\Ibexa\Validator\SiteAccessValidator;
use Netgen\Layouts\Tests\TestCase\ValidatorFactory as BaseValidatorFactory;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

final class ValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var array<string, \Symfony\Component\Validator\ConstraintValidatorInterface>
     */
    private array $validators;

    public function __construct(
        private BaseValidatorFactory $baseValidatorFactory,
        private Repository $repository,
    ) {
        $this->validators = [
            'nglayouts_ibexa_site_access' => new SiteAccessValidator(['eng', 'cro']),
            'nglayouts_ibexa_site_access_group' => new SiteAccessGroupValidator(
                [
                    'frontend' => ['eng'],
                    'backend' => ['admin'],
                ],
            ),
            'nglayouts_ibexa_content' => new ContentValidator($this->repository),
        ];
    }

    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $name = $constraint->validatedBy();

        return $this->validators[$name] ?? $this->baseValidatorFactory->getInstance($constraint);
    }
}
