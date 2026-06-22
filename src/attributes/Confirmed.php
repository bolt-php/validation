<?php

namespace framework\validation\attributes;

use Attribute;
use framework\validation\Validator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Confirmed extends Validator
{
    public $message = 'The value must match its confirmation value';

    public function __construct(protected string $confirmation, $message = '')
    {
        return parent::__construct($message);
    }

    public function validate($value, $doc = null): bool
    {
        $confirmation = $this->confirmation;
        return isset($doc->$confirmation) && $doc->$confirmation == $value;
    }

}