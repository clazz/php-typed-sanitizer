<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;

class UnsignedIntegerType extends IntegerType
{
    protected $type = 'unsigned integer';
    public function beforeApplyRules($value)
    {
        $value = parent::beforeApplyRules($value);
        if ($value < 0) {
            throw new InvalidTypeException($this, $value);
        }

        return $value;
    }
}
