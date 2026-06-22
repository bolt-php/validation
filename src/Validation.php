<?php

namespace framework\validation;

use stdClass;

class Validation
{
    protected static array $registry = [];

    public static function addValidator(string $name, callable|Validator $class)
    {
        static::$registry[$name] = $class;
    }

    public static function validate(stdClass $data, array $rules)
    {
        // Resolve registry
        foreach ($rules as $field => $validators) {
            if (is_string($validators)) {
                $validators = explode('|', $validators);
                $rules[$field] = $validators;
            }
            foreach ($validators as $key => $validator) {
                if (is_string($validator) && isset(static::$registry[$validator])) {
                    $rules[$field][$key] = static::$registry[$validator];
                }
            }
        }

        $errors = [];
        foreach ($rules as $field => $validators) {
            foreach ($validators as $validator) {
                if (is_callable($validator)) {
                    $result = $validator($data->$field ?? null);
                    if (!empty($result)) {
                        $errors[$field] = $result;
                        break;
                    }
                } elseif ($validator instanceof Validator) {
                    if (!$validator->validate($data->$field ?? null, $data)) {
                        $errors[$field] = $validator->message();
                        break;
                    }
                } elseif (\is_string($validator)) {
                    $validatorInstance = new $validator();
                    if (!$validatorInstance->validate($data->$field ?? null, $data)) {
                        $errors[$field] = $validatorInstance->message();
                        break;
                    }
                }
            }
        }
        return $errors;
    }
}