<?php

namespace App\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
#[\Attribute]
class OnlyDigit extends Constraint
{
    public $message = 'La valeur "{{ value }}" dans la liste "month_list" doit-être un nombre entier entre 1 et 12.';
}
