<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidJsonException;

class JsonType extends Type
{
    protected $type = 'JSON';
    protected $decodedType;

    public function __construct($decodedType = null)
    {
        $this->decodedType = $decodedType;
    }

    protected function beforeApplyRules($value)
    {
        $value = json_decode($value, true);
        if (json_last_error() != JSON_ERROR_NONE) {
            throw new InvalidJsonException($this, $value);
        }

        if ($this->decodedType) {
            return $this->decodedType->sanitize($value);
        } else {
            return $value;
        }
    }
}
