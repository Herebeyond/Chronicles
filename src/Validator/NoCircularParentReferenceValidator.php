<?php

namespace App\Validator;

use App\Entity\Idea;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class NoCircularParentReferenceValidator extends ConstraintValidator
{
    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof NoCircularParentReference) {
            throw new UnexpectedTypeException($constraint, NoCircularParentReference::class);
        }

        if (!$value instanceof Idea) {
            throw new UnexpectedTypeException($value, Idea::class);
        }

        // If no parent is set, no validation needed
        $parentIdea = $value->getParentIdea();
        if (!$parentIdea) {
            return;
        }

        // Get all descendant IDs of the current idea
        $descendantIds = $value->getAllDescendantIds();

        // Check if the selected parent is in the descendants
        if (in_array($parentIdea->getId(), $descendantIds, true)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ parentTitle }}', $parentIdea->getTitle())
                ->addViolation();
        }

        // Also check if trying to set itself as parent (shouldn't happen with form, but just in case)
        if ($value->getId() && $value->getId() === $parentIdea->getId()) {
            $this->context->buildViolation('Cannot set an idea as its own parent.')
                ->addViolation();
        }
    }
}
