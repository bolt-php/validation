<?php

namespace framework\validation\attributes;

use Attribute;
use framework\validation\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Number extends Validator
{
    public $message = 'This must be a valid number';

    public function validate($value, $_ = null): bool
    {
        return is_numeric($value);
    }

}