<?php

namespace framework\validation\attributes;

use Attribute;
use framework\validation\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Required extends Validator
{
    public $message = 'This field is required';

    public function validate($value, $_ = null): bool
    {
        return !empty($value);
    }

}