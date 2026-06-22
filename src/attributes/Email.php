<?php

namespace framework\validation\attributes;

use Attribute;
use framework\validation\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Email extends Validator
{
    public $message = 'Please enter a valid email';

    public function validate($value, $_ = null): bool
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false;
    }
}