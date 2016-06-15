<?php

namespace Clazz\Typed\Types;

class MobilePhoneType extends StringType
{
    protected $type = 'MobilePhone';

    const DEFAULT_PATTERN = '/^1[0-9]{10}?$/';

    public function __construct($pattern = null)
    {
        parent::__construct();

        $this->pattern($pattern ?: self::DEFAULT_PATTERN);
    }
}
