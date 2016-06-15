<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;

class DoubleType extends NumericType
{
    protected $type = 'double';
    protected $pattern = '/^-?(\d+)?\.?(\d+)?$/';

    public function beforeApplyRules($value)
    {
        if (preg_match($this->pattern, $value)) {
            return doubleval($value);
        }

        throw new InvalidTypeException($this, $value);
    }
}
