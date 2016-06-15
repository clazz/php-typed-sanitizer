<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidInstanceException;

class InstanceOfType extends Type
{
    protected $type = 'InstanceOf';
    protected $classOrInterface;

    public function __construct($classOrInterface)
    {
        $this->classOrInterface = $classOrInterface;
        $this->type = "'".strval($classOrInterface)."'";
    }

    protected function beforeApplyRules($value)
    {
        if (!($value instanceof $this->classOrInterface)) {
            throw new InvalidInstanceException($this, $value);
        }

        return parent::beforeApplyRules($value);
    }
}
