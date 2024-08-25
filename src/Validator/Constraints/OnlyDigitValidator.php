<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class OnlyDigitValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        // Vérify if list element are int between 1 and 12
        foreach ($value as $item) {
            if (!is_int($item) || $item < 1 || $item > 12) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('{{ value }}', $item)
                    ->addViolation();
            }
        }
    }
}
