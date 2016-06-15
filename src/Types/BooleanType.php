<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;

class BooleanType extends Type
{
    protected $type = 'boolean';
    protected $boolValues = [
        'yes' => true,
        'no' => false,
        'on' => true,
        'off' => false,
        'true' => true,
        'false' => false,
    ];

    protected function beforeApplyRules($value)
    {
        if ($value === Type::undefinedValue()) {
            return $value;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return $value != 0;
        }

        if (is_null($value)) {
            return false;
        }

        if (strlen($value) < 10) {
            $value = strtolower($value);
            if (isset($this->boolValues[$value])) {
                return $this->boolValues[$value];
            }
        }

        throw new InvalidTypeException($this, $value);
    }
}
