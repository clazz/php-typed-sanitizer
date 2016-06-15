<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidTypeException;
use Clazz\Typed\Exceptions\ValueTooBigException;
use Clazz\Typed\Exceptions\ValueTooSmallException;

class NumericType extends Type
{
    protected $type = 'numeric';

    public function beforeApplyRules($value)
    {
        if (!is_numeric($value)) {
            throw new InvalidTypeException($this, $value);
        }

        return $value;
    }

    public function isInRange($min, $max)
    {
        return $this->addRule(function ($value) use ($min, $max) {
            if ($value < $min) {
                throw new ValueTooSmallException($this, $min);
            }

            if ($value > $max) {
                throw new ValueTooBigException($this, $max);
            }

            return $value;
        });
    }

    public function inRange($min, $max)
    {
        return $this->isInRange($min, $max);
    }
}
