<?php

declare(strict_types=1);

namespace Netgen\Layouts\Ibexa\Validator;

use Ibexa\Contracts\Core\Repository\Repository;
use Netgen\Layouts\Ibexa\Validator\Constraint\ObjectState;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

use function array_key_exists;
use function count;
use function explode;
use function in_array;
use function is_array;
use function is_string;

/**
 * Validates if the provided value is an identifier of a valid object state in Ibexa CMS.
 */
final class ObjectStateValidator extends ConstraintValidator
{
    /**
     * @var array<string, string[]>
     */
    private array $stateIdentifiers = [];

    public function __construct(private Repository $repository) {}

    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($value === null) {
            return;
        }

        if (!$constraint instanceof ObjectState) {
            throw new UnexpectedTypeException($constraint, ObjectState::class);
        }

        if (!is_string($value)) {
            throw new UnexpectedTypeException($value, 'string');
        }

        $originalValue = $value;
        $value = explode('|', $value);
        if (count($value) !== 2) {
            throw new UnexpectedTypeException($originalValue, 'string with "|" delimiter');
        }

        $stateIdentifiers = $this->loadStateIdentifiers();

        if (!array_key_exists($value[0], $stateIdentifiers)) {
            $this->context->buildViolation($constraint->invalidGroupMessage)
                ->setParameter('%identifier%', $value[0])
                ->addViolation();

            return;
        }

        if (!in_array($value[1], $stateIdentifiers[$value[0]], true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%identifier%', $value[0])
                ->addViolation();

            return;
        }

        if (count($constraint->allowedStates) === 0) {
            return;
        }

        if (
            !array_key_exists($value[0], $constraint->allowedStates)
            || $constraint->allowedStates[$value[0]] === true
        ) {
            return;
        }

        if (
            is_array($constraint->allowedStates[$value[0]])
            && in_array($value[1], $constraint->allowedStates[$value[0]], true)
        ) {
            return;
        }

        $this->context->buildViolation($constraint->notAllowedMessage)
            ->setParameter('%identifier%', $value[1])
            ->setParameter('%groupIdentifier%', $value[0])
            ->addViolation();
    }

    /**
     * Returns the list of object state identifiers separated by group.
     *
     * @return array<string, string[]>
     */
    private function loadStateIdentifiers(): array
    {
        if ($this->stateIdentifiers === []) {
            $this->stateIdentifiers = $this->repository->sudo(
                function (): array {
                    $stateIdentifiers = [];

                    $stateGroups = $this->repository->getObjectStateService()->loadObjectStateGroups();
                    foreach ($stateGroups as $stateGroup) {
                        $stateIdentifiers[$stateGroup->identifier] = [];

                        $states = $this->repository->getObjectStateService()->loadObjectStates($stateGroup);
                        foreach ($states as $state) {
                            $stateIdentifiers[$stateGroup->identifier][] = $state->identifier;
                        }
                    }

                    return $stateIdentifiers;
                },
            );
        }

        return $this->stateIdentifiers;
    }
}
