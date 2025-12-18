<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Tests\Validator;

use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Validator\ContentTypeValidator;
use Netgen\Layouts\Ibexa\Validator\ContentValidator;
use Netgen\Layouts\Ibexa\Validator\LocationValidator;
use Netgen\Layouts\Ibexa\Validator\ObjectStateValidator;
use Netgen\Layouts\Ibexa\Validator\SectionValidator;
use Netgen\Layouts\Ibexa\Validator\SiteAccessGroupValidator;
use Netgen\Layouts\Ibexa\Validator\SiteAccessValidator;
use Netgen\Layouts\Ibexa\Validator\TagValidator;
use Netgen\TagsBundle\API\Repository\TagsService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

final class ValidatorFactory implements ConstraintValidatorFactoryInterface
{
    private ConstraintValidatorFactory $baseValidatorFactory;

    /**
     * @var array<string, \Symfony\Component\Validator\ConstraintValidatorInterface>
     */
    private array $validators;

    public function __construct(
        private Repository $repository,
        private TagsService $tagsService,
    ) {
        $this->baseValidatorFactory = new ConstraintValidatorFactory();

        $this->validators = [
            'nglayouts_ibexa_location' => new LocationValidator($this->repository),
            'nglayouts_ibexa_content' => new ContentValidator($this->repository),
            'nglayouts_ibexa_content_type' => new ContentTypeValidator($this->repository),
            'nglayouts_ibexa_section' => new SectionValidator($this->repository),
            'nglayouts_ibexa_object_state' => new ObjectStateValidator($this->repository),
            'nglayouts_ibexa_site_access' => new SiteAccessValidator(['eng', 'cro']),
            'nglayouts_ibexa_site_access_group' => new SiteAccessGroupValidator(
                ['frontend' => ['eng'], 'backend' => ['admin']],
            ),
            'nglayouts_netgen_tags' => new TagValidator($this->tagsService),
        ];
    }

    public function getInstance(Constraint $constraint): ConstraintValidatorInterface
    {
        $name = $constraint->validatedBy();

        return $this->validators[$name] ?? $this->baseValidatorFactory->getInstance($constraint);
    }
}
