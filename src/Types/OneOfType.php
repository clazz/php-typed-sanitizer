<?php

namespace Clazz\Typed\Types;

use Clazz\Typed\Exceptions\InvalidInstanceException;
use Clazz\Typed\Exceptions\VerificationFailedException;

class OneOfType extends Type
{
    protected $type = 'OneOf';
    protected $types = [];

    public function __construct($types)
    {
        foreach ($types as $type) {
            if ($type instanceof Type) {
                $this->types[] = $type;
            } else {
                $this->types[] = Type::of($type);
            }
        }

        $typeNames = [];
        foreach ($this->types as $type) {
            $typeNames[] = $type->type;
        }

        $this->type = 'OneOf('.implode(', ', $typeNames).')';
    }

    protected function beforeApplyRules($value)
    {
        $gotValue = Type::undefinedValue();

        foreach ($this->types as $type) {
            try {
                $gotValue = $type->sanitize($value);
                break;
            } catch (VerificationFailedException $e) {
            }
        }

        if ($gotValue === Type::undefinedValue()) {
            throw new InvalidInstanceException($this, $value);
        }

        return parent::beforeApplyRules($gotValue);
    }
}
