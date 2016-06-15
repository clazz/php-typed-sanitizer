<?php

namespace Clazz\Typed\Types;

class JsonObjectType extends JsonType
{
    protected $type = 'JSON-Object';

    public function __construct($fields = [])
    {
        parent::__construct(new ArrayType($fields));
    }
}
