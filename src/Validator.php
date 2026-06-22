<?php

namespace framework\validation;

/**
 * Defines a validator interface used to perform
 * different validations on a single given value
 */
abstract class Validator {
    protected $message = '';

    public function __construct($message = '')
    {
        if (!empty($message)) {
            $this->message = $message;
        }
    }

    /**
     * Validates the given value, with an optional data model
     * for cross-validation
     * @param mixed $value
     * @param mixed $data
     * @return boolean true if the validation succeeds, false otherwise
     */
    public abstract function validate($value, $data = null): bool;

    public function message()
    {
        $msg = $this->message;
        foreach (get_object_vars($this) as $key => $value) {
            if (is_scalar($value)) {
                $msg = str_replace("{" . $key . "}", $value, $msg);
            }
        }
        return $msg;
    }
}