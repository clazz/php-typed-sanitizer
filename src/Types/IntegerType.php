<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;

class IntegerType extends NumericType
{
    protected $type = 'integer';
    protected $pattern = '/^-?\d+$/';
    public function beforeApplyRules($value)
    {
        if (preg_match($this->pattern, $value)) {
            return intval($value);
        }

        throw new InvalidTypeException($this, $value);
    }
}
