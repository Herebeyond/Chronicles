<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

#[\Attribute]
class NoCircularParentReference extends Constraint
{
    public string $message = 'Cannot set "{{ parentTitle }}" as parent because it would create a circular reference (this idea is already an ancestor of that idea).';

    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
