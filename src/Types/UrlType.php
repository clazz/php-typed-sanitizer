<?php

namespace Clazz\Typed\Types;

class UrlType extends StringType
{
    protected $type = 'url';

    const DEFAULT_PATTERN = '~^http(s?)://[^/ ]+(/\S+)?$~';

    public function __construct($pattern = null)
    {
        parent::__construct();

        $this->pattern($pattern ?: self::DEFAULT_PATTERN);
    }
}
