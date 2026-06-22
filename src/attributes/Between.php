<?php

namespace framework\validation\attributes;

use Attribute;
use framework\validation\Validator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_METHOD)]
class Between extends Validator
{
    public $message = 'This field must be between {min} and {max}';
    public $min = 0;
    public $max = 255;

    public function __construct($min = 0, $max = 255, $message = '')
    {
        $this->min = $min;
        $this->max = $max;
        parent::__construct($message);
    }

    public function validate($value, $_ = null): bool
    {
        return $value >= $this->min && $value <= $this->max;
    }

}