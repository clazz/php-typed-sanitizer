<?php

namespace Clazz\Typed\Types;

class JsonArrayType extends JsonType
{
    protected $type = 'JSON-Array';

    public function __construct($fields = [])
    {
        parent::__construct((new ArrayType())->isListOf($fields));
    }
}
