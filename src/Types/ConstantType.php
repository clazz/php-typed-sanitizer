<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidInstanceException;

class ConstantType extends Type
{
    protected $type = 'constant';
    protected $constantValue;
    public function __construct($constantValue)
    {
        $this->constantValue = $constantValue;
        if (is_object($constantValue)) {
            $this->type = 'Constant(Object...)';
        } elseif (is_array($constantValue)) {
            $this->type = 'Constant(Array...)';
        } else {
            $this->type = 'Constant('.json_encode($constantValue).')';
        }

        $this->addRule(function ($value) {
            if ($value !== $this->constantValue) {
                throw new InvalidInstanceException($this, $value);
            }

            return $value;
        });
    }
}
