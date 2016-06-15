<?php

namespace Clazz\Typed\Types;

class StringType extends Type
{
    protected $type = 'string';
    protected function beforeApplyRules($value)
    {
        return strval(parent::beforeApplyRules($value));
    }
}
