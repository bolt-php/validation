<?php

namespace framework\validation;

use framework\validation\interfaces\TypeTransformer;

class Model
{
    public $errors = [];

    /**
     * @var TypeTransformer[]
     */
    protected static $typeTransformers = [];

    public static function registerTypeTransformer(string $type, TypeTransformer $transformer)
    {
        static::$typeTransformers[$type] = $transformer;
    }

    protected static function hasProperty($name)
    {
        $cls = \get_called_class();
        $reflection = new \ReflectionClass($cls);
        return $reflection->hasProperty($name);
    }

    public static function basename()
    {
        $cls = \get_called_class();
        $cls = explode('\\', $cls);
        return end($cls);
    }

    public static function from(...$data): static
    {
        $cls = \get_called_class();
        $model = new $cls();
        $model->fill(...$data);
        return $model;
    }

    public static function getMetaData()
    {
        $cls = \get_called_class();
        $reflection = new \ReflectionClass($cls);
        $properties = $reflection->getProperties();
        $metaData = [];

        foreach ($properties as $property) {
            // Only proceed if the property was defined in the current class ($cls)
            if ($property->getDeclaringClass()->getName() === $cls) {
                $metaData[$property->getName()] = [
                    'name' => $property->getName(),
                    'type' => (string) $property->getType(),
                    'initialized' => function ($instance) use ($property) {
                        return $property->isInitialized($instance);
                    },
                    'attributes' => $property->getAttributes()
                ];
            }
        }

        return $metaData;
    }

    public function load($data)
    {
        $base = static::basename();
        if (is_array($data) && isset($data[$base])) {
            $data = $data[$base];
        }
        $meta = static::getMetaData();
        foreach ($meta as $key => $info) {
            if (!isset($data[$key]))
                continue;

            $type = $info['type'];
            if (isset(static::$typeTransformers[$type])) {
                // Let the transformer decide if this value should be skipped
                if (static::$typeTransformers[$type]->isEmpty($data[$key])) {
                    continue;
                }
                $this->{$key} = static::$typeTransformers[$type]->transformFromDatabase($data[$key]);
            } else if ($type == 'DateTime') {
                $this->{$key} = new \DateTime($data[$key]);
            } else {
                $this->{$key} = $data[$key] ?? null;
            }
        }
    }

    public function fill(array ...$data)
    {
        foreach ($data as $item) {
            $this->load($item);
        }
        return $this;
    }

    /**
     * Instance Methods
     */
    public function rules()
    {
        return [];
    }

    public function validate()
    {
        $metaData = self::getMetaData();
        $rules = self::rules();

        foreach ($metaData as $property => $data) {
            foreach ($data['attributes'] as $attribute) {
                $instance = $attribute->newInstance();
                $rules[$property][] = $instance;
            }
        }

        $this->errors = app()->validator->validate($this, $rules);
        return empty($this->errors);
    }

    public function errors($name = '')
    {
        if ($name) {
            return $this->errors[$name] ?? '';
        }
        return $this->errors;
    }

    public function error($name, $message)
    {
        $this->errors[$name] = $message;
    }

    public static function label($property)
    {
        $metaData = self::getMetaData();
        if (isset($metaData[$property])) {
            foreach ($metaData[$property]['attributes'] as $attribute) {
                $instance = $attribute->newInstance();
                if (method_exists($instance, 'label')) {
                    return $instance->label();
                }
            }
        }
        return ucfirst($property);
    }

    /**
     * Safe getter for properties
     * Returns null if property is not initialized
     * 
     * @param mixed $name
     */
    public function safe_get($name)
    {
        $refProp = new \ReflectionProperty($this, $name);
        return $refProp->isInitialized($this)
            ? $refProp->getValue($this)
            : null;
    }
}