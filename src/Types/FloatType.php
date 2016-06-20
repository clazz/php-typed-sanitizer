<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;

class FloatType extends NumericType
{
    protected $type = 'float';
    protected $pattern = '/^-?(\d+)?\.?(\d+)?$/';

    public function beforeApplyRules($value)
    {
        if (!is_object($value) && !is_array($value) && preg_match($this->pattern, $value)) {
            return floatval($value);
        }

        throw new InvalidTypeException($this, $value);
    }
}
